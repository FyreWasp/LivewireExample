@props([
    'delete'      => false,
    'id'          => '',
    'key'         => '',
    'path'        => '',
    'resource'    => '',
    'triggerName' => '',
    'update'      => false,
    'value'       => null,
])

<x-card
    :delete="$delete"
    :deleteText="__('order.'.Services::EDUCATION.'.card.educatorDelete')"
    :has-errors="$errors->has($path.'.*')"
    :id="$id"
    :key="$key"
    :message="trans_choice('order.card.error', $errors->countKeys($path.'.*'), ['number' => $errors->countKeys($path.'.*')])"
    :resource="$resource"
    :title="$value->schoolName"
    :triggerName="$triggerName"
    :update="$update"
>
    <x-card.slot
        :has-error="$errors->has($path.'.degree')"
        :label="__('order.'.Services::EDUCATION.'.card.degree')"
        :value="$value->degree"
    />

    @if (!empty($value->major))
        <x-card.slot
            :has-error="$errors->has($path.'.major')"
            :label="__('order.'.Services::EDUCATION.'.card.major')"
            :value="$value->major"
        />
    @endif

    @if (!empty($value->minor))
        <x-card.slot
            :has-error="$errors->has($path.'.minor')"
            :label="__('order.'.Services::EDUCATION.'.card.minor')"
            :value="$value->minor"
        />
    @endif

    <x-card.slot
        :has-error="$errors->has($path.'.graduated')"
        :label="__('order.'.Services::EDUCATION.'.card.graduated')"
        :value="display_boolean($value->graduated, '')"
    />

    @if (!empty($value->branch))
        <x-card.slot
            :has-error="$errors->has($path.'.branch')"
            :label="__('order.'.Services::EDUCATION.'.card.branch')"
            :value="$value->branch"
        />
    @endif

    <x-card.slot
        :has-error="$errors->hasAny([$path.'.startDate', $path.'.endDate'])"
        :label="__('order.'.Services::EDUCATION.'.card.date')"
    >
        <x-formatted-date :start="$value->startDate" :end="$value->endDate" />
    </x-card.slot>

    <x-card.slot
        :has-error="$errors->hasAny([$path.'.city', $path.'.region', $path.'.postalCode', $path.'.countryCode'])"
        :label="__('order.'.Services::EDUCATION.'.card.address')"
    >
        <x-formatted-address
            :city="$value->city ?? ''"
            :region="$value->region ?? ''"
            :postalCode="$value->postalCode ?? ''"
            :countryCode="$value->countryCode ?? ''"
        />
    </x-card.slot>

    @if (!empty($value->comments))
        <x-card.slot
            class="col-span-12"
            :label="__('order.'.Services::EDUCATION.'.card.comments')"
            :value="$value->comments"
        />
    @endif
</x-card>
