<?php

namespace App\Http\Livewire\Services;

use App\MSAResources\Educator;
use App\Services\InvitationService;
use App\Services\OrderService;
use App\Traits\WithCollectionRange;
use App\Traits\WithCountryList;
use App\Traits\WithDegreeList;
use App\Traits\WithMajorMinorList;
use App\Traits\WithOrderable;
use App\Traits\WithStateList;
use App\Validation\OrderValidator;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Education extends Component
{
    /*
     * Provides compiled information about an order so that component templates
     * have access to all common order options, validation, and data.
     */
    use WithOrderable {
        cancelChanges as cancelOrderableChanges;
        getClean as getOrderableClean;
        getDirty as getOrderableDirty;
    }

    /*
     * Support order collection ranges within components.
     */
    use WithCollectionRange;

    /*
     * Provides a component access to dropdown lists.
     */
    use WithCountryList;
    use WithDegreeList;
    use WithMajorMinorList;
    use WithStateList;

    /**
     * @const string Name of route controller is implicitly bound to
     */
    public const ROUTE_NAME = 'order.education';

    /**
     * @var string template view used module
     */
    public const TEMPLATE = 'livewire.order.education';

    /**
     * @var bool does this module display a link back to applicant summary
     */
    public $returnableToApplicantSummary = true;

    /**
     * @var Educator new education entry to display in the add educator form
     */
    public Educator $newEducator;

    /**
     * method to run all necessary steps to set up the component before it gets rendered.
     *
     * @param OrderService      $orderService      an instance of the OrderService for retrieving order data
     * @param InvitationService $invitationService an instance of the InvitationService for retrieving invitation data
     * @param string            $orderId           the order id to get the associated order information
     *
     * @return void
     */
    public function mount(OrderService $orderService, InvitationService $invitationService, string $orderId)
    {
        $this->loadOrderable($orderService, $invitationService, $orderId);
        $this->resetNewEducator();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render()
    {
        return view(self::TEMPLATE);
    }

    /**
     * Save a new educator to the order.
     *
     * @return bool
     */
    public function saveNewEducator()
    {
        $this->orderDirty->education->push($this->newEducator);
        $status = $this->saveOrder();

        if ($status) {
            $status = $this->resetNewEducator();
        }

        return $status;
    }

    /**
     * Cancels any changes made to the new educator.
     *
     * @return bool
     */
    public function resetNewEducator()
    {
        $this->newEducator = Educator::make();

        return true;
    }

    /**
     * Livewire lifecycle hook that runs any time the NewEducator property is updated from the UI. We use it here to
     * run live validation when creating a new education entry.
     *
     * @param string|array $value the value of the input that triggered the update method to be fired
     * @param string       $name  the name of the input that triggered the update method to be fired
     *
     * @return void sets error bag when new form validation errors are thrown
     */
    public function updatedNewEducator($value, string $name)
    {
        $this->newEducator->formatForDisplay();

        $requirements = $this->findRequirements();
        $validator    = new OrderValidator($this->newEducator, $requirements, 'education');
        $attributes   = array_keys(Arr::where($this->newEducator->toArray(), function ($value, $key) {
            return '' !== trim($value);
        }));

        if (!in_array($name, $attributes)) {
            $attributes[] = $name;
        }

        try {
            $validator->validateOnly($attributes);
        } catch (ValidationException $validationException) {
            $this->setErrorBag($validator->errors());
        }
    }

    /**
     * Return "clean" order or Educator object.
     *
     * @param string $propertyName name of the field that fired the update process
     *
     * @return App\MSAResources\Order "clean" order or Educator object
     */
    public function getClean($propertyName)
    {
        if (false !== strpos($propertyName, 'newEducator')) {
            $clean = Educator::make();

            $clean->educationId = $this->newEducator->educationId;
        } else {
            $clean = $this->getOrderableClean();
        }

        return $clean;
    }

    /**
     * Return "dirty" order or newEducator object.
     *
     * @param string $propertyName name of the field that fired the update process
     *
     * @return App\MSAResources\Order "dirty" order or newEducator object
     */
    public function getDirty($propertyName)
    {
        return false !== strpos($propertyName, 'newEducator') ? $this->newEducator : $this->getOrderableDirty();
    }

    /**
     * Override WithOrderable's cancelChanges method so that we can include page specific resets.
     *
     * @return bool
     */
    public function cancelChanges()
    {
        $status = $this->resetNewEducator();

        if ($status) {
            $status = $this->cancelOrderableChanges();
        }

        return $status;
    }
}
