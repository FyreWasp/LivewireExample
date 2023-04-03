<?php

namespace App\Traits;

use App\Constants\Order as Navigation;
use App\Constants\Toast;
use App\Facades\MaxLength;
use App\Helpers\ResourceSorter;
use App\Http\Livewire\Order\ApplicationSummary;
use App\Http\Livewire\Order\Consent;
use App\Http\Livewire\Order\OrderComplete;
use App\Http\Livewire\Order\ServicesReview;
use App\MSAResources\Order;
use App\Services\InvitationService;
use App\Services\OrderService;
use App\Settings\OrderSettings;
use App\Support\MessageBag;
use App\Validation\OrderValidator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use stdClass;

/**
 * Livewire trait that will provide a component with combined information about an order.
 *
 * This includes information about:
 *     * The order
 *     * The invitation
 *     * The validation of the order
 */
trait WithOrderable
{
    /**
     * @var OrderSettings provides compiled information about an order
     */
    protected $orderSettings;

    /**
     * @var OrderService provides actions against an order
     */
    protected $orderService;

    /**
     * @var InvitationService provides actions against an Invitation
     */
    protected $invitationService;

    /**
     * @var collection information about this and surrounding pages
     *
     * page data will be formatted like this:
     *
     * collection([
     *     0 => {
     *         'service'  => Services::PERSONAL,
     *         'step'     => 0,
     *         'route'    => static::ROUTE_NAME,
     *         'url       => route(static::ROUTE_NAME, $orderId),
     *         'title'    => trans('order.'.Services::PERSONAL.'.title'),
     *         'complete' => 1,
     *         'total'    => 5,
     *     },
     *     1 => {
     *         'service'  => Services::ADDRESS,
     *         'step'     => 1,
     *         'route'    => static::ROUTE_NAME,
     *         'url'      => route(static::ROUTE_NAME, $orderId),
     *         'title'    => trans('order.'.Services::ADDRESS.'.title'),
     *         'complete' => 3,
     *         'total'    => 3,
     *     },
     * ])
     */
    public $pages;

    /**
     * @var array information to display in the page's header
     */
    public $header = [
        'companyName' => '',
        'headerTitle' => '',
        'backURL'     => '',
        'returnTitle' => '',
    ];

    /**
     * @var string the invitation's introduction to the order
     */
    public $introduction = '';

    /**
     * @var string the next url in the page's workflow of an order
     */
    public $previousUrl = '';

    /**
     * @var string the next url in the page's workflow of an order
     */
    public $nextUrl = '';

    /**
     * @var string the previouis page title in the page's workflow of an order
     */
    public $previousTitle = '';

    /**
     * @var string the next page title in the page's workflow of an order
     */
    public $nextTitle = '';

    /**
     * @var string name of the resource that is stored in this trait
     */
    public $resourceName = '';

    /**
     * @var Order the instance of the order containing all display data
     */
    public Order $order;

    /**
     * @var Order a cloned instance of the order to use for form data so that it can be wired without impacting display
     */
    public Order $orderDirty;

    /**
     * @var bool is the order object currently clean
     */
    public $orderClean = true;

    /**
     * @var bool does the order contain only supported services
     */
    public $areServicesSupported = false;

    /**
     * Make sure we always have access to the order service.
     *
     * @param OrderService      $orderService      performs order actions against the transporter
     * @param InvitationService $invitationService provides information about an invitation
     *
     * @return void
     */
    public function hydrateWithOrderable(OrderService $orderService, InvitationService $invitationService)
    {
        $this->orderService      = $orderService;
        $this->invitationService = $invitationService;
    }

    /**
     * Mount combined information about an order from multiple transporter endpoints.
     *
     * @param OrderService      $orderService      provides information about a service
     * @param InvitationService $invitationService provides information about an invitation
     * @param string            $orderId           identifier of the order we need to retrieve
     *
     * @return void
     */
    public function loadOrderable(OrderService $orderService, InvitationService $invitationService, string $orderId)
    {
        $this->pages = collect([]);

        $this->orderService      = $orderService;
        $this->invitationService = $invitationService;

        $this->order = $this->orderService->find($orderId) ?? abort(404);
        $this->order->formatForDisplay();

        ResourceSorter::sort($this->order);

        $this->orderDirty = clone $this->order;

        if (!empty($this->order)) {
            $invitation = $this->invitationService->find($this->order->inviteId) ?? abort(404);
            if (!empty($invitation)) {
                $this->orderSettings = new OrderSettings($this->order, $invitation, static::class);
                $this->populate();
            }
        }
    }

    /**
     * Runs on every request, after any update methods are called, but before the component is rendered.
     *
     * @return void
     */
    public function renderingWithOrderable(): void
    {
        MaxLength::setOrderId($this->order->orderId);

        $newFormErrors = new MessageBag($this->getErrorBag()->getMessages());

        $this->setErrorBag(new MessageBag());

        $requirements = $this->findRequirements();
        $validator    = new OrderValidator($this->orderDirty, $requirements);

        try {
            $validator->validate();
        } catch (ValidationException $e) {
            $errorBag = new MessageBag($validator->errors()->messages(), $validator->failed());
            $this->setErrorBag($errorBag);
            $this->getErrorBag()->merge($newFormErrors);
        }
    }

    /**
     * Save any changes that the consumer has made to the initially loaded order.
     *
     * @return bool
     */
    public function saveOrder()
    {
        $updated = $this->orderService->update($this->orderDirty);

        ResourceSorter::sort($this->orderDirty);

        if ($updated) {
            $this->order      = clone $this->orderDirty;
            $this->orderClean = true;
        } else {
            $this->emit('toast', trans('order.errors.saveOrder'), Toast::ERROR);
        }

        return $updated;
    }

    /**
     * get clean object for comparison.
     *
     * @param string $propertyName name of the field that fired the update process
     *
     * @return App\MSAResources\Order Clean object for comparison
     */
    public function getClean($propertyName = null)
    {
        return $this->order;
    }

    /**
     * get dirty object for comparison.
     *
     * @param string $propertyName name of the field that fired the update process
     *
     * @return App\MSAResources\Order Dirty object for comparison
     */
    public function getDirty($propertyName = null)
    {
        return $this->orderDirty;
    }

    /**
     * Retrieves an order's requirements.
     *
     * @return stdClass
     */
    public function findRequirements(): stdClass
    {
        $invitationId = $this->order->inviteId;

        return $this->invitationService->findRequirements($invitationId);
    }

    /**
     * Function called by livewire when an input has been updated.
     *
     * @param string $path  dot notated path to the field that fired the update process
     * @param mixed  $value value of the field that fired the update process
     *
     * @return void
     */
    public function updatedWithOrderable($path, $value)
    {
        if (Str::of($path)->before('.')->exactly('orderDirty')) {
            $this->orderDirty->formatForDisplay();
        }

        $this->orderClean = ($this->getDirty($path) == $this->getClean($path));
    }

    /**
     * Cancel any changes that have been made to the order but not yet saved.
     *
     * @return bool
     */
    public function cancelChanges()
    {
        $this->orderDirty = clone $this->order;

        $this->orderClean = true;

        return true;
    }

    /**
     * Delete a resource from the order.
     *
     * @param string $resourceName name of the resource to locate
     * @param string $id           identifier value of the resource to locate
     *
     * @return bool
     */
    public function deleteResource($resourceName, $id)
    {
        $status = false;

        foreach ($this->orderDirty as $value) {
            if ($value instanceof \Illuminate\Support\Collection) {
                $status = $this->deleteResourceFromCollection($value, $resourceName, $id);
                if ($status) {
                    break;
                }
            } elseif ($value instanceof \App\MSAResources\MSAResource) {
                foreach ($value as $childValue) {
                    if ($childValue instanceof \Illuminate\Support\Collection) {
                        $status = $this->deleteResourceFromCollection($childValue, $resourceName, $id);
                        if ($status) {
                            break;
                        }
                    }
                }
                if ($status) {
                    break;
                }
            }
        }

        if ($status) {
            $status = $this->saveOrder();
        }

        return $status;
    }

    /**
     * Search for a specific resource in a collection and remove it.
     *
     * @param Collection $collection   list of resources to comb through
     * @param string     $resourceName name of the resource to locate
     * @param string     $id           identifier value of the resource to locate
     *
     * @return bool
     */
    protected function deleteResourceFromCollection($collection, $resourceName, $id)
    {
        $status = false;
        foreach ($collection as $index => $resource) {
            if ($resource instanceof $resourceName) {
                if ($id === $resource->{$resource->getKeyName()}) {
                    $collection->forget($index);
                    $status = true;
                    break;
                }
            }
        }

        return $status;
    }

    /**
     * Populate the component page with all of the gathered settings so they are accessible.
     *
     * @return void
     */
    public function populate()
    {
        $this->pages                = $this->orderSettings->getPages();
        $this->header               = $this->orderSettings->getHeader();
        $this->introduction         = $this->orderSettings->getIntroduction();
        $this->areServicesSupported = $this->orderSettings->areServicesSupported();

        // pages to not set previous or next urls programmatically because they are not dependent on the order of
        // services associated with the order
        $nonServicePages = [
            ApplicationSummary::ROUTE_NAME,
            ServicesReview::ROUTE_NAME,
            Consent::ROUTE_NAME,
            OrderComplete::ROUTE_NAME,
        ];

        if (!in_array(static::ROUTE_NAME, $nonServicePages)) {
            $previousNav         = $this->getNavigation(Navigation::NAVIGATION_PREVIOUS);
            $nextNav             = $this->getNavigation(Navigation::NAVIGATION_NEXT);
            $this->nextUrl       = $nextNav['url'];
            $this->nextTitle     = $nextNav['title'];
            $this->previousUrl   = $previousNav['url'];
            $this->previousTitle = $previousNav['title'];
        }
    }

    /**
     * Retrieve the next url in the page's workflow of an order.
     *
     * @return array
     */
    public function getNavigation($type)
    {
        $increment                = $type;
        $orderId                  = $this->orderSettings->getOrder()->orderId;
        $incrementStep            = $this->pages->firstWhere('route', static::ROUTE_NAME)->step;
        $incrementPage            = $this->pages->firstWhere('step', ($incrementStep + $increment));
        $incrementRoute           = route('order.application-summary', $orderId);
        $navigation['title']      = trans('order.summary.title');

        if (!empty($incrementPage)) {
            $incrementRoute      = route($incrementPage->route, $orderId);
            $navigation['title'] = $incrementPage->titleAlias;
        } else {
            $navigation['title'] = trans('order.summary.title');
        }
        $navigation['url']   = $incrementRoute;

        return $navigation;
    }
}
