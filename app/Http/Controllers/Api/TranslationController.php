<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\TranslationResource;
use App\Models\Locale;
use App\Models\Tag;
use App\Models\Translation;
use Illuminate\Support\Facades\DB;

class TranslationController extends Controller
{


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

    public function show(Translation $translation)
    {
        return new TranslationResource($translation->load(['values.locale', 'tags']));
    }

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
