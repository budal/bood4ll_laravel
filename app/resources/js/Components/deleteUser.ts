import { toast } from 'vue3-toastify';
import { selectedItems, selectedItemsUUIDs, form, props, closeModal } from './Table.vue';

export const deleteUser = () => {
selectedItems.forEach((item: any) => {
selectedItemsUUIDs.add(item.uuid);
form.uuids.push(item.uuid);
});

form.delete(route(props.destroyRoute), {
preserveScroll: true,
onSuccess: () => closeModal(),
onError: () => toast.error('Wow so easy !'),
onFinish: () => toast.success(props.status),
});
};
