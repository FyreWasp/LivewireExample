<?php

namespace App\Traits;

use App\Services\OptionsService;

/**
 * Livewire trait providing a component access to the degree list.
 */
trait WithDegreeList
{
    // Formats collections for use in a template's dropdown menu.
    use WithDropdownList;

    /**
     * Boot this trait with a list of degrees formatted for a dropdown.
     *
     * @param OptionsService $optionsService service providing access to dropdown lists
     *
     * @return void
     */
    public function bootedWithDegreeList(OptionsService $optionsService)
    {
        $degreeList = $optionsService->getDegreeList();

        $this->options = $this->options->merge([
            'degrees' => $this->formatDropdownList($degreeList, 'degree', 'degree'),
        ]);
    }
}
