<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Registro de ocorrência') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <section>
                    <header>
                        <h2 class="text-lg font-medium text-gray-900">
                            {{ __('Cadastro de ocorrência') }}
                        </h2>

                        <p class="mt-1 text-sm text-gray-600">
                            {{ __("Registre os dados da ocorrência.") }}
                        </p>
                    </header>

                    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                        @csrf
                    </form>

                    <form method="post" action="{{ route('calls.insert') }}" class="mt-6 space-y-6">
                        @csrf
                        @method('patch')

                    <div class="flex flex-wrap -mx-3 mb-6">
                            <div class="w-full md:w-1/6 px-3 mb-6 md:mb-0">
                                <x-input-label for="phone" :value="__('Telefone')" />
                                <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $user->phone)" required autofocus autocomplete="phone" />
                                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                            </div>
                            <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                                <x-input-label for="fullname" :value="__('Nome do solicitante')" />
                                <x-text-input id="fullname" name="fullname" type="text" class="mt-1 block w-full" :value="old('fullname', $user->fullname)" required autofocus autocomplete="fullname" />
                                <x-input-error class="mt-2" :messages="$errors->get('fullname')" />
                            </div>
                            <div class="w-full md:w-1/4 px-3">
                                <x-input-label for="city" :value="__('Município')" />
                                <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" :value="old('city', $user->city)" required autocomplete="city" />
                                <x-input-error class="mt-2" :messages="$errors->get('city')" />
                            </div>
                            <div class="w-full md:w-1/4 px-3">
                                <x-input-label for="neighborhood" :value="__('Bairro')" />
                                <x-text-input id="neighborhood" name="neighborhood" type="text" class="mt-1 block w-full" :value="old('neighborhood', $user->neighborhood)" required autocomplete="neighborhood" />
                                <x-input-error class="mt-2" :messages="$errors->get('email')" />
                            </div>
                        </div>

                        <div class="flex flex-wrap -mx-3 mb-6">
                            <div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
                                <x-input-label for="address" :value="__('Logradouro')" />
                                <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" :value="old('address', $user->address)" required autofocus autocomplete="address" />
                                <x-input-error class="mt-2" :messages="$errors->get('address')" />
                            </div>
                            <div class="w-full md:w-1/6 px-3 mb-6 md:mb-0">
                                <x-input-label for="number" :value="__('Número')" />
                                <x-text-input id="number" name="number" type="text" class="mt-1 block w-full" :value="old('number', $user->number)" required autofocus autocomplete="number" />
                                <x-input-error class="mt-2" :messages="$errors->get('number')" />
                            </div>
                            <div class="w-full md:w-1/3 px-3">
                                <x-input-label for="reference" :value="__('Ponto de referência')" />
                                <x-text-input id="reference" name="reference" type="text" class="mt-1 block w-full" :value="old('reference', $user->reference)" required autocomplete="reference" />
                                <x-input-error class="mt-2" :messages="$errors->get('reference')" />
                            </div>
                        </div>

                        
                        <div class="flex flex-wrap -mx-3 mb-6">
                            <div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
                                <x-input-label for="cornerwith" :value="__('Esquina com')" />
                                <x-text-input id="cornerwith" name="cornerwith" type="text" class="mt-1 block w-full" :value="old('cornerwith', $user->cornerwith)" required autofocus autocomplete="cornerwith" />
                                <x-input-error class="mt-2" :messages="$errors->get('cornerwith')" />
                            </div>
                            <div class="w-full md:w-1/2 px-3">
                                <x-input-label for="crime" :value="__('Possível natureza')" />
                                <x-text-input id="crime" name="crime" type="text" class="mt-1 block w-full" :value="old('crime', $user->crime)" required autocomplete="crime" />
                                <x-input-error class="mt-2" :messages="$errors->get('crime')" />
                            </div>
                        </div>

                        <div class="flex flex-wrap -mx-3 mb-6">
                            <div class="w-full md:w-1/1 px-3 mb-6 md:mb-0">
                                <x-input-label for="description" :value="__('Descrição inicial')" />
                                <x-textarea id="description" name="description" type="text" class="mt-1 block w-full" :value="old('description', $user->description)" required autofocus autocomplete="description" />
                                <x-input-error class="mt-2" :messages="$errors->get('description')" />
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Salvar ocorrência') }}</x-primary-button>

                            @if (session('status') === 'profile-updated')
                                <p
                                    x-data="{ show: true }"
                                    x-show="show"
                                    x-transition
                                    x-init="setTimeout(() => show = false, 2000)"
                                    class="text-sm text-gray-600"
                                >{{ __('Salva.') }}</p>
                            @endif
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
