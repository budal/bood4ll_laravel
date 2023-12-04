<script lang="ts">
    import { usePage } from '@inertiajs/vue3';
    import { transChoice } from 'laravel-vue-i18n';
    import { ReplacementsInterface } from 'laravel-vue-i18n/interfaces/replacements';
    import { ToastType, toast } from 'vue3-toastify';

    const alert = () => {
        if (usePage().props.toast_message) {
            toast(
                transChoice(
                    usePage().props.toast_message as string, 
                    (usePage().props.toast_count || 0) as number,
                    (usePage().props.toast_replacements || []) as ReplacementsInterface
                ),
                {
                    type: (usePage().props.toast_type || 'info') as ToastType
                }
            );
        }
    }

    export default alert
</script>