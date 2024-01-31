<script setup>
import {onMounted, ref, watch} from "vue";
import {useRoute} from 'vue-router';

import { useBrandStore } from '../../stores/store-brands'

import VhViewRow from '../../vaahvue/vue-three/primeflex/VhViewRow.vue';
const store = useBrandStore();
const route = useRoute();
onMounted(async () => {

    /**
     * If record id is not set in url then
     * redirect user to list view
     */
    if(route.params && !route.params.id)
    {
        store.toList();
        return false;
    }

    /**
     * Fetch the record from the database
     */
    if(!store.item || Object.keys(store.item).length < 1)
    {
        await store.getItem(route.params.id);
    }

    /**
     * Watch if url record id is changed, if changed
     * then fetch the new records from database
     */
    /*watch(route, async (newVal,oldVal) =>
        {
            if(newVal.params && !newVal.params.id
                && newVal.name === 'articles.view')
            {
                store.toList();

            }
            await store.getItem(route.params.id);
        }, { deep: true }
    )*/

});

//--------toggle item menu
const item_menu_state = ref();
const toggleItemMenu = (event) => {
    item_menu_state.value.toggle(event);
};
//--------/toggle item menu

</script>
<template>

    <div class="col-6" >

        <Panel class="is-small" v-if="store && store.item">

            <Message severity="info" :closable="false" v-if="store.item.status_notes">
                <div style="width:350px;overflow-wrap: break-word;word-wrap:break-word;">
                    <pre v-html="store.item.status_notes"></pre>
                </div>
            </Message>

            <template class="p-1" #header>

                <div class="flex flex-row">

                    <div class="p-panel-title">
                        #{{store.item.id}}
                    </div>

                </div>

            </template>

            <template #icons>


                <div class="p-inputgroup">
                    <Button label="Edit"
                            class="p-button-sm"
                            @click="store.toEdit(store.item)"
                            :disabled="!store.assets.permissions.includes('can-update-module')"
                            data-testid="brands-item-to-edit"
                            icon="pi pi-save"/>

                    <!--item_menu-->
                    <Button
                        type="button"
                        class="p-button-sm"
                        @click="toggleItemMenu"
                        :disabled="!store.assets.permissions.includes('can-update-module')"
                        data-testid="brands-item-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="item_menu_state"
                          :model="store.item_menu_list"
                          :popup="true" />
                    <!--/item_menu-->

                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="brands-item-to-list"
                            @click="store.toList()"/>

                </div>



            </template>


            <div class="mt-2" v-if="store.item">

                <Message severity="error"
                         class="p-container-message"
                         :closable="false"
                         icon="pi pi-trash"
                         v-if="store.item.deleted_at">

                    <div class="flex align-items-center justify-content-between">

                        <div class="">
                            Trashed {{store.item.deleted_at}}
                        </div>

                        <div class="ml-3">
                            <Button label="Restore"
                                    class="p-button-sm"
                                    data-testid="brands-item-restore"
                                    :disabled="!store.assets.permissions.includes('can-update-module')"
                                    @click="store.itemAction('restore')">
                            </Button>
                        </div>

                    </div>

                </Message>

                <div class="p-datatable p-component p-datatable-responsive-scroll p-datatable-striped p-datatable-sm">
                    <table class="p-datatable-table">
                        <tbody class="p-datatable-tbody">
                        <template v-for="(value, column) in store.item ">

                            <template v-if=" column === 'created_by'
                                     ||  column === 'status'
                                     ||  column === 'status_notes'
                                     ||  column === 'updated_by'
                                     ||  column === 'image'
                                     ||  column === 'meta_title'
                                     ||  column === 'meta_description'
                                     ||  column === 'meta_keywords'
                                     ||  column === 'registered_by'
                                     ||  column === 'registered_at'
                                     ||  column === 'registered_by_user'
                                     ||  column === 'approved_by_user'
                                     ||  column === 'approved_by'
                                     ||  column === 'approved_at'
                                     ||  column === 'is_default'
                                     ||  column ==='deleted_by'
                                     ||  column === 'meta'
                                     ||  column === 'taxonomy_id_brand_status'
                                        ">
                            </template>

                            <template v-else-if="column === 'id' || column === 'uuid'">
                                <VhViewRow :label="column"
                                           :value="value"
                                           :can_copy="true"
                                />
                            </template>

                            <template v-else-if="column === 'slug' ">
                                <VhViewRow :label="column"
                                           :value="value"
                                           type="slug"
                                />

                                <VhViewRow label="Brand Logo"
                                           :value="store.item.image"
                                           type="image_preview"

                                />
                                <VhViewRow label="Registered By"
                                           :value="store.item.registered_by_user"
                                           type="register-approve"
                                />
                                <VhViewRow label="Registered At"
                                           :value="store.item.registered_at"

                                />

                                <VhViewRow label="Approved By"
                                           :value="store.item.approved_by_user"
                                           type="register-approve"
                                />
                                <VhViewRow label="Approved At"
                                           :value="store.item.approved_at"

                                />
                            </template>

                            <template v-else-if="(column === 'created_by_user' || column === 'updated_by_user'
                         || column === 'deleted_by_user') && (typeof value === 'object' && value !== null)">
                                <VhViewRow :label="column"
                                           :value="value"
                                           type="user"
                                />
                            </template>


                            <template v-else-if="column === 'is_active'">
                                <tr>
                                    <td><b>Meta</b></td>
                                    <td>
                                        <Button icon="pi pi-eye"
                                                label="view"
                                                class="p-button-outlined p-button-secondary p-button-rounded p-button-sm"
                                                @click="store.openModal()"
                                                :disabled="!store.item.meta_title &&
                                            !store.item.meta_description &&
                                            !(store.item && store.item.meta_keywords && Array.isArray(store.item.meta_keywords) &&
                                             store.item.meta_keywords.length > 0)"

                                                data-testid="meta-open_modal"
                                        />
                                    </td>
                                </tr>

                                <VhViewRow label="Is Active"
                                           :value="value"
                                           type="yes-no"
                                />

                                <VhViewRow label="Status"
                                           :value="store.item.status"
                                           type="status"
                                />
                            </template>

                            <template v-else-if="column === 'is_default'">
                                <VhViewRow :label="column"
                                           :value="value"
                                           type="yes-no"
                                />
                            </template>

                            <template v-else>
                                <VhViewRow :label="column"
                                           :value="value"
                                />
                            </template>

                        </template>
                        </tbody>

                    </table>

                </div>
            </div>
        </Panel>

    </div>

    <Dialog header="Meta Fields"
            v-model:visible="store.display_meta_modal"
            :breakpoints="{'960px': '75vw', '640px': '90vw'}"
            :style="{width: '50vw'}" :modal="true"
    >
        <div class="mb-4 flex"><span class="font-bold mr-2">Meta Title: </span><p>{{store.item.meta_title}}</p></div>
        <div class="mb-4 flex">
            <span class="font-bold mr-2" style="margin-top: 0.8rem;">Meta Description:</span>
            <pre style="font-family: Inter,ui-sans-serif">{{store.item.meta_description}}</pre>
        </div>
        <div class="flex"><span class="font-bold mr-2">Meta Keyword: </span> <p>{{store.item.meta_keywords}}</p></div>
    </Dialog>

</template>
