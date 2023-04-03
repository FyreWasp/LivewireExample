<?php

namespace App\Traits;

use App\Services\OptionsService;

/**
 * Livewire trait providing a component access to the majors and minors for a degree.
 */
trait WithMajorMinorList
{
    // Formats collections for use in a template's dropdown menu.
    use WithDropdownList;

    /**
     * Boot this trait with a list of majors and minors formatted for dropdowns.
     *
     * @param OptionsService $optionsService service providing access to dropdown lists
     *
     * @return void
     */
    public function bootedWithMajorMinorList(OptionsService $optionsService)
    {
        $majorList = $optionsService->getMajorList();
        $minorList = $optionsService->getMinorList();

        $this->options = $this->options->merge([
            'majors' => $this->formatDropdownList($majorList, 'major', 'major'),
            'minors' => $this->formatDropdownList($minorList, 'minor', 'minor'),
        ]);
    }
}
