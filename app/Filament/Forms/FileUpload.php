<?php

namespace App\Filament\Forms;

use Filament\Forms\Components\FileUpload as BaseFileUpload;

/**
 * Filament's upload box picks its wording from the app language, and in
 * Indonesian that reads "Seret & Jatuhkan berkas Anda atau Jelajahi". Those
 * are technical terms people know in English, so this box always says
 * "Drag & Drop your files or Browse".
 *
 * The labels are baked into Filament's JavaScript and chosen by the app locale
 * while the field renders, so the locale is switched to English for just that
 * moment. It is bound over Filament's own class in AppServiceProvider, so every
 * upload field in the admin (and the rich editor's image upload) gets it.
 */
class FileUpload extends BaseFileUpload
{
    public function toEmbeddedHtml(): string
    {
        $locale = app()->getLocale();

        app()->setLocale('en');

        try {
            return parent::toEmbeddedHtml();
        } finally {
            app()->setLocale($locale);
        }
    }
}
