<div>
    @livewire('partials.header', $header)

    @if(!empty($collectionIntro))
        <div class="my-6 text-im-gray-500 text-sm">
            <p>{!! $collectionIntro !!}</p>
        </div>
    @endif

    <div x-data="formManager(@entangle('orderClean'))">
        <x-confirmationDialog
                message="{{ __('order.confirmation.message') }}"
                yesClick="cancelConfirmed"
        />

        <div id="education_list">
            <h2 class="text-md text-im-blue-500 mb-6">{{ __('order.'.Services::EDUCATION.'.title') }}</h2>
            @forelse($order->education as $educator)
                <x-educator.card
                        delete
                        :id="'educator-'.$educator->educationId"
                        :key="$educator->educationId"
                        :path="'education.'.$loop->index"
                        :resource="\App\MSAResources\Educator::class"
                        :trigger-name="'edu'.$loop->index.'Form'"
                        update
                        :value="$educator"
                />
                <x-slider :triggerName="'edu'.$loop->index.'Form'">
                    <x-educator.form
                            :options="$options"
                            :path="'education.'.$loop->index"
                            :trigger-name="'edu'.$loop->index.'Form'"
                            :wire="'orderDirty.education.'.$loop->index"
                    />
                </x-slider>
            @empty
                <div class="text-gray-500 text-sm mb-8">{{ __('order.'.Services::EDUCATION.'.empty') }}</div>
            @endforelse
        </div>

        <div
                x-data="{ showAddButton: {{ json_encode(!$collectionRangeConfirmed) }} }"
                @input-changed-confirmed-gaps="showAddButton = !$event.detail"
        >
            @if(!$collectionMaxMet)
                <div x-cloak x-show="showAddButton" id="education-add-new">
                    <button
                            class="w-full py-16 rounded-xl border-dashed border-light-blue-500 border text-gray-500 text-sm uppercase"
                            @click="$store.slider.show('eduNewForm')"
                    >
                        + {{  __('order.'.Services::EDUCATION.'.form.educatorCreate')  }}
                    </button>

                    <x-slider triggerName="eduNewForm">
                        <x-educator.form new wire="newEducator" :options="$options" triggerName="eduNewForm" />
                    </x-slider>
                </div>
                @if(!empty($collectionIntro))
                    <x-switchbox
                            id="confirmed-gaps"
                            name="confirmed-gaps"
                            class="mb-4 p-2 mt-4"
                            wire:model="collectionRangeConfirmed"
                    >
                        {{ trans_choice('order.education.confirmGaps', $order->education->count()) }}
                    </x-switchbox>
                @endif
            @endif
        </div>
    </div>

    <div class="grid grid-cols-12 gap-4 mt-16">
        <div class="col-span-6">
            <x-button class="w-full" instant onclick="window.location='{{ $previousUrl }}'" icon-left="fa fa-chevron-left">{{ $previousTitle }}</x-button>
        </div>
        <div class="col-span-6">
            <x-button class="w-full" instant onclick="window.location='{{ $nextUrl }}'" primary icon-right="fa fa-chevron-right" >{{ $nextTitle }} </x-button>
        </div>
    </div>
</div>
