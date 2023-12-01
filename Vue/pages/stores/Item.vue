<script setup>
import {onMounted, ref, watch} from "vue";
import {useRoute} from 'vue-router';

import { useStoreStore } from '../../stores/store-stores'

import VhViewRow from '../../vaahvue/vue-three/primeflex/VhViewRow.vue';
const store = useStoreStore();
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
                        <Tag class="tag-space" :value="store.item.id" style="border-radius:20px;padding:5px 10px;" />
                            <span style="margin-left:5px" v-if="store.item && store.item.name">{{ store.item.name.replace('.', '') }}</span>
                    </div>

                </div>

            </template>

            <template #icons>


                <div class="p-inputgroup">

                    <Button label="Edit"
                            class="p-button-sm"
                            @click="store.toEdit(store.item)"
                            data-testid="stores-item-to-edit"
                            icon="pi pi-save"/>

                    <!--item_menu-->
                    <Button
                        type="button"
                        class="p-button-sm"
                        @click="toggleItemMenu"
                        data-testid="stores-item-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="item_menu_state"
                          :model="store.item_menu_list"
                          :popup="true" />
                    <!--/item_menu-->

                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="stores-item-to-list"
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
                            Deleted {{store.item.deleted_at}}
                        </div>

                        <div class="ml-3">
                            <Button label="Restore"
                                    class="p-button-sm"
                                    data-testid="stores-item-restore"
                                    @click="store.itemAction('restore')">
                            </Button>
                        </div>

                    </div>

                </Message>

                <div class="p-datatable p-component p-datatable-responsive-scroll p-datatable-striped p-datatable-sm">
                <table class="p-datatable-table">
                    <tbody class="p-datatable-tbody">
                    <template v-for="(value, column) in store.item ">

                        <template v-if="column === 'created_by' || column === 'updated_by' || column === 'deleted_by'
                        || column === 'status' || column === 'status_notes' || column === 'currencies_data'
                         || column === 'lingual_data' || column === 'meta' || column === 'notes' || column === 'currency_default' || column === 'taxonomy_id_store_status'">
                        </template>

                        <template v-else-if="column === 'id' || column === 'uuid'">
                            <VhViewRow :label="column"
                                       :value="value"
                                       :can_copy="true"
                            />
                        </template>
                        <template v-else-if="column === 'name'">
                            <tr>
                                <td :style="{width: label_width}">
                                    <b>Name</b>
                                </td>
                                <td  colspan="2" >
                                    <div class="word-overflow" style="width:350px;overflow-wrap: break-word;word-wrap:break-word;">
                                        {{store.item.name}}</div>
                                </td>
                            </tr>
                        </template>
                        <template v-else-if="column === 'slug'">
                            <tr>
                                <td :style="{width: label_width}">
                                    <b>Slug</b>
                                </td>
                                <td  colspan="2" >
                                    <div class="word-overflow" style="width:350px;overflow-wrap: break-word;word-wrap:break-word;">
                                        {{store.item.slug}}</div>
                                </td>
                            </tr>
                            <tr>
                                <td :style="{width: label_width}">
                                    <b>Status</b>
                                </td>
                                <td  colspan="2" >
                                    <Tag v-if="store.item.status.name === 'Approved'"  severity="success" :value="store.item.status.name"  style="margin-top:10px;border-radius:20px;padding:5px 10px;"></Tag>
                                    <Tag v-else-if="store.item.status.name === 'Pending'"  severity="warning" :value="store.item.status.name"  style="margin-top:10px;border-radius:20px;padding:5px 10px;"></Tag>
                                    <Tag v-else-if="store.item.status.name === 'Rejected'"  severity="danger" :value="store.item.status.name"  style="margin-top:10px;border-radius:20px;padding:5px 10px;"></Tag>
                                    <Tag v-else severity="primary" :value="store.item.status.name"  style="margin-top:10px;border-radius:20px;padding:5px 10px;"></Tag>
                                </td>
                            </tr>
                        </template>

                        <template v-else-if="(column === 'created_by_user' || column === 'updated_by_user'  || column === 'deleted_by_user') && (typeof value === 'object' && value !== null)">
                            <VhViewRow :label="column"
                                       :value="value"
                                       type="user"
                            />
                        </template>

                        <template v-else-if="column === 'is_active'">
                            <VhViewRow label="Is Active"
                                       :value="value"
                                       type="yes-no"
                            />
                        </template>

                        <template v-else-if="column === 'is_default'">
                            <VhViewRow label="Is Default"
                                       :value="value"
                                       type="yes-no"
                            />
                        </template>

                        <template v-else-if="column === 'allowed_ips'">
                            <VhViewRow label="Allowed IPs"
                                       :value="value"
                                       type="allowedIps"
                            />
                        </template>

                        <template v-else-if="column === 'currencies'">
                            <tr>
                                <td :style="{width: label_width}">
                                    <b>Currencies</b>
                                </td>
                                <td  colspan="2" v-if="store.item.is_multi_currency">
                                    <AutoComplete name="store-currencies"
                                                  data-testid="store-currencies"
                                                  v-model="store.item.currencies"
                                                  option-label ="name"
                                                  multiple
                                                  :placeholder="store.item.currencies.length === 0 ? 'Select currencies' : ''"
                                                  :complete-on-focus = "true"
                                                  :suggestions="store.currency_suggestion_list"
                                                  @change = "store.saveCurrencies()"
                                                  @complete="store.searchCurrencies"
                                                  class="w-full"
                                    />
                                </td>
                            </tr>

                        </template>

                        <template v-else-if="column === 'default_currency'">
                            <tr>
                                <td :style="{width: label_width}">
                                    <b>Default Currency</b>
                                </td>
                                <td  colspan="2" v-if="store.item.default_currency && store.item.default_currency.name">
                                    <div class="word-overflow" style="width:350px;overflow-wrap: break-word;word-wrap:break-word;">
                                        <Tag :severity="primary" :value="store.item.default_currency.name" :rounded="true" style="border-radius:20px;padding:5px 10px;">
                                        </Tag>
                                    </div>

                                </td>
                                <td  colspan="2" v-else>

                                </td>
                            </tr>
                        </template>

                        <template v-else-if="column === 'languages'">
                            <tr>
                                <td :style="{width: label_width}">
                                    <b>Languages</b>
                                </td>
                                <td  colspan="2" v-if="store.item.is_multi_lingual">
                                    <AutoComplete name="store-languages"
                                                  data-testid="store-languages"
                                                  v-model="store.item.languages"
                                                  option-label = "name"
                                                  multiple
                                                  :complete-on-focus = "true"
                                                  :suggestions="store.language_suggestion_list"
                                                  :placeholder="store.item.languages.length === 0 ? 'Select languages' : ''"
                                                  @change = "store.saveLanguages()"
                                                  @complete="store.searchLanguages"
                                                  class="w-full"
                                    />

                                </td>
                            </tr>
                        </template>

                        <template v-else-if="column === 'default_language'">
                            <tr>
                                <td :style="{width: label_width}">
                                    <b>Default Language</b>
                                </td>
                                <td  colspan="2" v-if="store.item.default_language && store.item.default_language.name">
                                    <div class="word-overflow" style="width:350px;overflow-wrap: break-word;word-wrap:break-word;">
                                        <Tag :severity="primary" :value="store.item.default_language.name" :rounded="true" style="border-radius:20px;padding:5px 10px;"></Tag>
                                    </div>
                                </td>
                                <td v-else>

                                </td>
                            </tr>
                        </template>

                        <template v-else-if="column === 'is_multi_currency'">
                            <VhViewRow label="Is Multi Currency"
                                       :value="value"
                                       type="yes-no"
                            />
                        </template>

                        <template v-else-if="column === 'is_multi_lingual'">
                            <VhViewRow label="Is Multi Language"
                                       :value="value"
                                       type="yes-no"
                            />
                        </template>

                        <template v-else-if="column === 'is_multi_vendor'">
                            <VhViewRow label="Is Multi Vendor"
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

</template>
