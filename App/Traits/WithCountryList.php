<?php

namespace App\Traits;

use App\Services\OptionsService;

/**
 * Livewire trait providing a component access to the country list.
 */
trait WithCountryList
{
    // Formats collections for use in a template's dropdown menu.
    use WithDropdownList;

    /**
     * Boot this trait with a list of countries formatted for a dropdown.
     *
     * @param OptionsService $optionsService service providing access to dropdown lists
     *
     * @return void
     */
    public function bootedWithCountryList(OptionsService $optionsService)
    {
        $countryList = $optionsService->getCountryList();

        $countryList->transform(function ($item) {
            $item->countryDisplay = "$item->countryCode: $item->countryName";

            return $item;
        });

        $this->options = $this->options->merge([
            'countries' => $this->formatDropdownList($countryList, 'countryDisplay', 'countryCode'),
        ]);
    }
}
