<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-slate-100">
            Eliminar Cuenta
        </h2>

        <p class="mt-1 text-sm text-slate-400">
            Una vez eliminada tu cuenta, todos sus recursos y datos serán borrados permanentemente. Antes de borrar tu cuenta, por favor descarga cualquier contrato o documento que desees conservar.
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >Eliminar Mi Cuenta</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-slate-100">
                ¿Estás seguro de que deseas eliminar tu cuenta de forma definitiva?
            </h2>

            <p class="mt-1 text-sm text-slate-400">
                Esta acción es irreversible. Por favor, introduce tu contraseña para confirmar la eliminación de tu cuenta.
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="Contraseña" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="Introduce tu contraseña"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Cancelar
                </x-secondary-button>

                <x-danger-button>
                    Confirmar y Eliminar Cuenta
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
