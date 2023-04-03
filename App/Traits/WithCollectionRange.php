<?php

namespace App\Traits;

use App\Constants\Order;
use App\Constants\Services;
use App\Constants\Toast;
use App\Services\InvitationService;
use App\Services\OrderService;

/**
 * Support order collection ranges within components.
 */
trait WithCollectionRange
{
    /**
     * @var InvitationService retrieves invitations from the transporter
     */
    protected InvitationService $collectionInvitationService;

    /**
     * @var OrderService retrieves orders from the transporter
     */
    protected OrderService $collectionOrderService;

    /**
     * @var string the order id being used to determine the collection range status
     */
    public string $collectionOrderId;

    /**
     * @var string the service name that this trait belongs to
     */
    public string $collectionService;

    /**
     * @var int minimum collection range of entries to collect
     */
    public int $collectionMin = 0;

    /**
     * @var int maximum collection range of entries to collect
     */
    public int $collectionMax = 0;

    /**
     * @var string message to display as an introduction for collection ranges
     */
    public string $collectionIntro = '';

    /**
     * @var bool true if the collection's maximum range has been met
     */
    public bool $collectionMaxMet = false;

    /**
     * @var bool represents the collection range gap confirmation in the ui from the order
     */
    public bool $collectionRangeConfirmed = false;

    /**
     * @var string Type if collection
     *
     * Y = range in years.
     * M = Min/Max Range of entries.
     */
    public string $collectionType = Order::COLLECTION_MIN_MAX;

    /**
     * Allow the serviceName to be set rather than defaulted from the invite.
     *
     * @param string $serviceInfoName service name for the serviceInfo node
     */
    private function setCollectionService(string $serviceInfoName)
    {
        $this->collectionService = $serviceInfoName;
    }

    /**
     * Set collectionType to years.
     *
     * @return void
     */
    public function setCollectionTypeToYears()
    {
        $this->collectionType = Order::COLLECTION_YEARS;
    }

    /**
     * Set collectionType to Min/Max.
     *
     * @return void
     */
    public function setCollectionTypeToMinMax()
    {
        $this->collectionType = Order::COLLECTION_MIN_MAX;
    }

    /**
     * Runs on every request, immediately after the component is instantiated,
     * but before any other lifecycle methods are called.
     *
     * @param OrderService      $orderService      retrieves orders from the transporter
     * @param InvitationService $invitationService retrieves invitations from the transporter
     *
     * @return void
     */
    public function bootWithCollectionRange(InvitationService $invitationService, OrderService $orderService): void
    {
        $this->collectionInvitationService = $invitationService;
        $this->collectionOrderService      = $orderService;
    }

    /**
     * Runs once, immediately after the component is instantiated, but before
     * render() is called. This is only called once on initial page load and
     * never called again, even on component refreshes.
     *
     * @param string $orderId the order id being used to determine the collection range status
     *
     * @return void
     */
    public function mountWithCollectionRange(string $orderId): void
    {
        $this->collectionOrderId = $orderId;

        if (empty($this->collectionService)) {
            $this->collectionService = array_flip(Services::SERVICES_TO_COMPONENTS)[static::class];
        }

        $this->processCollectionRange();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @param OrderService $orderService retrieves orders from the transporter
     *
     * @return View|Closure|string
     */
    public function renderingWithCollectionRange(OrderService $orderService): void
    {
        $order = $orderService->find($this->collectionOrderId) ?? abort(404);

        $this->collectionMaxMet = false;

        if (!empty($this->collectionMax)) {
            if ($order->{$this->collectionService}->count() >= $this->collectionMax) {
                $this->collectionMaxMet = true;
            }
        }
    }

    /**
     * Runs after any update to the Livewire component's data (Using wire:model,
     * not directly inside PHP).
     *
     * @param bool $value the state of the toggle
     *
     * @return void
     */
    public function updatedCollectionRangeConfirmed(bool $value): void
    {
        $order = $this->collectionOrderService->find($this->collectionOrderId) ?? abort(404);

        $order->serviceInfo->{$this->collectionService}->confirmedGaps = $value;
        if (!$this->orderService->update($order)) {
            $order->serviceInfo->{$this->collectionService}->confirmedGaps = !$value;

            $this->collectionRangeConfirmed = !$value;

            $this->emit('toast', trans('order.errors.saveOrder'), Toast::ERROR);
        }
    }

    /**
     * Process the collection range, introduction, and confirmation.
     *
     * @return void
     */
    protected function processCollectionRange(): void
    {
        $this->setCollectionRange();
        $this->setIntroContent();
    }

    /**
     *  set the min/max values from the invitation and populate min/max object properties.
     *
     * @return void
     */
    public function setCollectionRange()
    {
        $order = $this->collectionOrderService->find($this->collectionOrderId) ?? abort(404);

        if (!empty($order)) {
            $requirements                   = $this->collectionInvitationService->findRequirements($order->inviteId) ?? abort(404);
            $this->collectionRangeConfirmed = $order->serviceInfo->{$this->collectionService}->confirmedGaps;
            $serviceSettings                = (array) collect($requirements->components)->where('name', $this->collectionService)->first();

            if (!empty($serviceSettings['min']) || !empty($serviceSettings['max'])) {
                ['min' => $this->collectionMin, 'max' => $this->collectionMax] = $serviceSettings;
            }
        }
    }

    /**
     * set the intro content to display when rendering the page.
     *
     * @return void
     */
    protected function setIntroContent()
    {
        if (Order::COLLECTION_YEARS == $this->collectionType) {
            if ($this->collectionMin > 0) {
                $this->collectionIntro = trans_choice('order.'.$this->collectionService.'.introduction', $this->collectionMin);
            }
        } elseif (!empty($this->collectionMin) || !empty($this->collectionMax)) {
            $language = "order.$this->collectionService.introduction.min_max";
            $choice   = $this->collectionMax;
            if (empty($this->collectionMin)) {
                $language = "order.$this->collectionService.introduction.max_only";
                $choice   = $this->collectionMax;
            } elseif (empty($this->collectionMax)) {
                $language = "order.$this->collectionService.introduction.min_only";
                $choice   = $this->collectionMin;
            }

            $this->collectionIntro = trans_choice($language, $choice, ['min' => $this->collectionMin, 'max' => $this->collectionMax]);
        }
    }
}
