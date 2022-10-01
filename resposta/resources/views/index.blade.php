<x-guest-layout>
    <x-auth-card>
        <x-slot name="logo">
            <a href="/">
                <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
            </a>
        </x-slot>

        <div style="float: right">
            <a href="{{ route('register') }}">
                <x-primary-button class="ml-3">
                    {{ __('Register') }}
                </x-primary-button>
            </a>

            <a href="{{ route('login') }}">
                <x-primary-button class="ml-3">
                    {{ __('Log in') }}
                </x-primary-button>
            </a>
        </div>
    </x-auth-card>
</x-guest-layout>
