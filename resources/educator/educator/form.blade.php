@props([
    'new'         => false,
    'options'     => null,
    'path'        => '',
    'triggerName' => '',
    'wire'        => '',
])

@php
    if (!empty($path)) {
        $path = "$path.";
    }
@endphp

<h2 class="text-base text-im-blue-500 text-center mb-10">
    @if($new)
        {{ __('order.'.Services::EDUCATION.'.form.educatorCreate') }}
    @else
        {{ __('order.'.Services::EDUCATION.'.form.educatorUpdate') }}
    @endif
</h2>

<form
    x-data="current('current', 'endDate', @entangle("$wire.endDate"))"
    x-effect="updateCurrent()"
>
    <div class="modal-form-wrapper mb-10 grid grid-cols-12 gap-4">
        <div class="col-span-12">
            <x-switchbox
                name="current"
                class="text-sm"
            >
                {{ __('order.'.Services::EDUCATION.'.form.currentSchool') }}
            </x-switchbox>
        </div>

        <x-input
            class="col-span-6"
            label="{{ __('order.'.Services::EDUCATION.'.form.startDate') }}"
            :max-length="MaxLength::get('education.startDate')"
            name="startDate"
            :path="$path.'startDate'"
            wire:model.lazy="{{ $wire }}.startDate"
        />

        <x-input
            bind="endDateBindings"
            class="col-span-6"
            label="{{ __('order.'.Services::EDUCATION.'.form.endDate') }}"
            :max-length="MaxLength::get('education.endDate')"
            name="endDate"
            :path="$path.'endDate'"
            wire:model.lazy="{{ $wire }}.endDate"
        />

        <x-input
            class="col-span-12"
            label="{{ __('order.'.Services::EDUCATION.'.form.school') }}"
            :max-length="MaxLength::get('education.schoolName')"
            name="school-name"
            :path="$path.'schoolName'"
            wire:model.lazy="{{ $wire }}.schoolName"
        />

        <x-input
            class="col-span-12"
            label="{{ __('order.'.Services::EDUCATION.'.form.branch') }}"
            :max-length="MaxLength::get('education.branch')"
            name="branch"
            :path="$path.'branch'"
            wire:model.lazy="{{ $wire }}.branch"
        />

        <div class="col-span-12">
            <x-radio
                label="{{ __('order.'.Services::EDUCATION.'.form.graduated') }}"
                name="graduated"
                :path="$path.'graduated'"
            >
                <x-radio.option wire:model.lazy="{{ $wire }}.graduated" value="1">{{ __('common.yes') }}</x-radio.option>
                <x-radio.option wire:model.lazy="{{ $wire }}.graduated" value="0">{{ __('common.no') }}</x-radio.option>
            </x-radio>
        </div>

        <div class="form-input-container col-span-12">
            <x-select
                custom
                label="{{ __('order.'.Services::EDUCATION.'.form.degree') }}"
                :max-length="MaxLength::get('education.degree')"
                name="degree"
                :path="$path.'degree'"
                search
                wire:model.lazy="{{ $wire }}.degree"
            >
                @foreach ($options->get('degrees')->merge($this->customOption('degrees', "$wire.degree")) as $option)
                    <x-select.option :value="$option->value">{{ $option->display }}</x-select.option>
                @endforeach
            </x-select>
        </div>

        <div class="form-input-container col-span-12">
            <x-select
                custom
                label="{{ __('order.'.Services::EDUCATION.'.form.major') }}"
                :max-length="MaxLength::get('education.major')"
                name="major"
                :path="$path.'major'"
                search
                wire:model.lazy="{{ $wire }}.major"
            >
                @foreach ($options->get('majors')->merge($this->customOption('majors', "$wire.major")) as $option)
                    <x-select.option :value="$option->value">{{ $option->display }}</x-select.option>
                @endforeach
            </x-select>
        </div>

        <div class="form-input-container col-span-12">
            <x-select
                custom
                label="{{ __('order.'.Services::EDUCATION.'.form.minor') }}"
                :max-length="MaxLength::get('education.minor')"
                name="minor"
                :path="$path.'minor'"
                search
                wire:model.lazy="{{ $wire }}.minor"
            >
                @foreach ($options->get('minors')->merge($this->customOption('minors', "$wire.minor")) as $option)
                    <x-select.option :value="$option->value">{{ $option->display }}</x-select.option>
                @endforeach
            </x-select>
        </div>

        <x-input
            class="col-span-12"
            label="{{ __('order.'.Services::EDUCATION.'.form.city') }}"
            :max-length="MaxLength::get('education.city')"
            name="city"
            :path="$path.'city'"
            wire:model.lazy="{{ $wire }}.city"
        />

        <div class="form-input-container col-span-6">
            <x-select
                label="{{ __('order.'.Services::EDUCATION.'.form.state') }}"
                name="region"
                :path="$path.'region'"
                search
                wire:model.lazy="{{ $wire }}.region"
            >
                @foreach ($options->get('states') as $option)
                    <x-select.option :value="$option->value">{{ $option->display }}</x-select.option>
                @endforeach
            </x-select>
        </div>

        <x-input
            class="col-span-6"
            label="{{ __('order.'.Services::EDUCATION.'.form.postalCode') }}"
            :max-length="MaxLength::get('education.postalCode')"
            name="postal-code"
            :path="$path.'postalCode'"
            wire:model.lazy="{{ $wire }}.postalCode"
        />

        <div class="form-input-container col-span-12">
            <x-select
                label="{{ __('order.'.Services::EDUCATION.'.form.countryCode') }}"
                name="countryCode"
                :path="$path.'countryCode'"
                search
                wire:model.lazy="{{ $wire }}.countryCode"
            >
                @foreach ($options->get('countries') as $option)
                    <x-select.option :value="$option->value">{{ $option->display }}</x-select.option>
                @endforeach
            </x-select>
        </div>

        <x-textarea
            class="col-span-12"
            label="{{ __('order.'.Services::EDUCATION.'.form.comments') }}"
            :max-length="MaxLength::get('education.comments')"
            name="comments"
            :path="$path.'comments'"
            wire:model.lazy="{{ $wire }}.comments"
        />
    </div>

    <div class="grid grid-cols-12 gap-4">
        <div class="col-span-6 mt-6">
            <x-button
                :triggerName="$triggerName"
                action="cancelForm"
                actionParam="{{ $triggerName }}"
                actionType="data"
                class="w-full"
                icon-left="fas fa-ban"
            >
                {{ __('common.cancel') }}
            </x-button>
        </div>

        <div class="col-span-6 mt-6">
            <x-button :triggerName="$triggerName" :action="$new ? 'saveNewEducator' : 'saveOrder'" :processing="$new ? __('common.saving') : __('common.updating')" class="w-full"  primary icon-right="fas fa-save">
                @if($new)
                   {{ __('common.save') }}
                @else
                    {{ __('common.update') }}
                @endif
            </x-button>
        </div>
    </div>
</form>
