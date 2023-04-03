<?php

namespace App\Traits;

use App\Http\Livewire\Order\Addresses;
use App\Http\Livewire\Order\Criminal;
use App\Http\Livewire\Order\ProfessionalLicenses;
use App\Services\OptionsService;

/**
 * Livewire trait providing a component access to the state list.
 */
trait WithStateList
{
    // Formats collections for use in a template's dropdown menu.
    use WithDropdownList;

    /**
     * @var collection collection of state information for the form
     */
    public $stateOptions;

    /**
     * Boot this trait with a list of states formatted for a dropdown.
     *
     * @param OptionsService $optionsService service providing access to dropdown lists
     *
     * @return void
     */
    public function bootedWithStateList(OptionsService $optionsService)
    {
        $filters = match (get_class($this)) {
            Addresses::class,
            Criminal::class             => OptionsService::RESIDENCE_STATELIST_CRITERIA,
            ProfessionalLicenses::class => OptionsService::PROFESSIONAL_LICENSE_STATELIST_CRITERIA,
            default                     => OptionsService::DEFAULT_STATELIST_CRITERIA,
        };

        $stateList = $optionsService->getStateList($filters);
        $stateList->transform(function ($item) {
            $item->stateDisplay = "$item->stateCode: $item->stateName";

            return $item;
        });

        $this->options = $this->options->merge([
            'states' => $this->formatDropdownList($stateList, 'stateDisplay', 'stateCode'),
        ]);
    }
}
