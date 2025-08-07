<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\TranslationResource;
use App\Models\Locale;
use App\Models\Tag;
use App\Models\Translation;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class TranslationController extends Controller
{


    #[OA\Get(
        path: "/api/translations",
        summary: "List translations with optional filters",
        tags: ["Translations"],
        security: [["passport" => []]],
        parameters: [
            new OA\Parameter(name: "tag", in: "query", description: "Filter by tag name", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "key", in: "query", description: "Filter by translation key (partial match)", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "content", in: "query", description: "Filter by translation value (partial match)", schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "List of translations",
                content: new OA\JsonContent(type: "array", items: new OA\Items(ref: "#/components/schemas/Translation"))
            ),
            new OA\Response(response: 401, description: "Unauthorized")
        ]
    )]
    public function index(Request $request)
    {
        $query = Translation::with(['values.locale', 'tags']);

        if ($request->has('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('name', 'like', '%'. $request->tag . '%');
            });
        }

        if ($request->has('key')) {
            $query->where('key', 'like', '%' . $request->key . '%');
        }

        if ($request->has('content')) {
            $query->whereHas('values', function ($q) use ($request) {
                $q->where('value', 'like', '%' . $request->content . '%');
            });
        }

        return TranslationResource::collection($query->get());
    }

    #[OA\Post(
        path: "/api/translations",
        summary: "Create a new translation",
        tags: ["Translations"],
        security: [["passport" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["key", "values"],
                properties: [
                    new OA\Property(property: "key", type: "string", example: "greeting"),
                    new OA\Property(
                        property: "values",
                        type: "object",
                        example: ["en" => "Hello", "fr" => "Bonjour", "es" => "Hola"]
                    ),
                    new OA\Property(
                        property: "tags",
                        type: "array",
                        items: new OA\Items(type: "string"),
                        example: ["mobile", "web"]
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Translation created",
                content: new OA\JsonContent(ref: "#/components/schemas/Translation")
            ),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store(Request $request)
    {
        $request->validate([
            'key' => 'required|string|unique:translations',
            'values' => 'required|array',
            'values.*' => 'required|string',
            'tags' => 'array',
            'tags.*' => 'exists:tags,name',
        ]);

        return DB::transaction(function () use ($request) {
            $translation = Translation::create(['key' => $request->key]);
            // dD($request->values); // en, fr, hello
            foreach ($request->values as $localeCode => $value) {
                $locale = Locale::where('code', $localeCode)->firstOrFail();
                $translation->values()->create([
                    'locale_id' => $locale->id,
                    'value' => $value,
                ]);
            }

            if ($request->has('tags')) {
                $tags = Tag::whereIn('name', $request->tags)->pluck('id');
                $translation->tags()->attach($tags);
            }

            return new TranslationResource($translation->load(['values.locale', 'tags']));
        });
    }

    #[OA\Get(
        path: "/api/translations/{id}",
        summary: "View a specific translation",
        tags: ["Translations"],
        security: [["passport" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Translation details",
                content: new OA\JsonContent(ref: "#/components/schemas/Translation")
            ),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Translation not found")
        ]
    )]
    public function show(Translation $translation)
    {
        return new TranslationResource($translation->load(['values.locale', 'tags']));
    }

        #[OA\Put(
        path: "/api/translations/{id}",
        summary: "Update an existing translation",
        tags: ["Translations"],
        security: [["passport" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "key", type: "string", example: "greeting"),
                    new OA\Property(
                        property: "values",
                        type: "object",
                        example: ["en" => "Hello", "fr" => "Bonjour", "es" => "Hola"]
                    ),
                    new OA\Property(
                        property: "tags",
                        type: "array",
                        items: new OA\Items(type: "string"),
                        example: ["mobile", "web"]
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Translation updated",
                content: new OA\JsonContent(ref: "#/components/schemas/Translation")
            ),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Translation not found"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function update(Request $request, Translation $translation)
    {
        $request->validate([
            'key' => 'string|unique:translations,key,' . $translation->id,
            'values' => 'array',
            'values.*' => 'string',
            'tags' => 'array',
            'tags.*' => 'exists:tags,name',
        ]);

        return DB::transaction(function () use ($request, $translation) {
            if ($request->has('key')) {
                $translation->update(['key' => $request->key]);
            }

            if ($request->has('values')) {
                foreach ($request->values as $localeCode => $value) {
                    $locale = Locale::where('code', $localeCode)->firstOrFail();
                    $translation->values()->updateOrCreate(
                        ['locale_id' => $locale->id],
                        ['value' => $value]
                    );
                }
            }

            if ($request->has('tags')) {
                $tags = Tag::whereIn('name', $request->tags)->pluck('id');
                $translation->tags()->sync($tags);
            }

            return new TranslationResource($translation->load(['values.locale', 'tags']));
        });
    }

    #[OA\Get(
        path: "/api/translations/export",
        summary: "Export all translations in JSON format",
        tags: ["Translations"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Exported translations",
                content: new OA\JsonContent(
                    example: [
                        "greeting" => [
                            "en" => "Hello",
                            "fr" => "Bonjour",
                            "es" => "Hola"
                        ]
                    ]
                )
            )
        ]
    )]
    public function export()
    {
        $translations = Translation::with(['values.locale', 'tags'])->get();
        $export = [];

        foreach ($translations as $translation) {
            $export[$translation->key] = $translation->values->mapWithKeys(function ($value) {
                return [$value->locale->code => $value->value];
            })->all();
        }

        return response()->json($export);
    }
}
