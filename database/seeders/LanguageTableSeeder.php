<?php

namespace Database\Seeders;


use App\Enums\Status;
use App\Models\Language;
use Illuminate\Database\Seeder;
use App\Enums\DisplayMode;


class LanguageTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $englishLanguageArray = [
            'name'              => 'English',
            'code'              => 'en',
            'display_mode'      => DisplayMode::LTR,
            'status'            => Status::ACTIVE
        ];

        $russianLanguageArray = [
            'name'              => 'Russian',
            'code'              => 'ru',
            'display_mode'      => DisplayMode::LTR,
            'status'            => Status::ACTIVE
        ];

        $azerbaijaniLanguageArray = [
            'name'              => 'Azerbaijani',
            'code'              => 'az',
            'display_mode'      => DisplayMode::LTR,
            'status'            => Status::ACTIVE
        ];

        $englishLanguage = Language::create($englishLanguageArray);
        if(file_exists(public_path('/images/language/english.png'))) {
            $englishLanguage->addMedia(public_path('/images/language/english.png'))->preservingOriginal()->toMediaCollection('language');
        }

        $russianLanguage = Language::create($russianLanguageArray);
        if(file_exists(public_path('/images/language/russian.png'))) {
            $russianLanguage->addMedia(public_path('/images/language/russian.png'))->preservingOriginal()->toMediaCollection('language');
        }

        $azerbaijaniLanguage = Language::create($azerbaijaniLanguageArray);
        if (file_exists(public_path('/images/language/azerbaijani.png'))) {
            $azerbaijaniLanguage->addMedia(public_path('/images/language/azerbaijani.png'))->preservingOriginal()->toMediaCollection('language');
        }

    }
}
