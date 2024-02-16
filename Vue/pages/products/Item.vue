<script setup>
import {onMounted, ref, watch} from "vue";
import {useRoute} from 'vue-router';

import { useProductStore } from '../../stores/store-products'

import VhViewRow from '../../vaahvue/vue-three/primeflex/VhViewRow.vue';
const store = useProductStore();
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
                <pre style="word-break:break-word;overflow-wrap:break-word;word-wrap:break-word;white-space:pre-wrap;">{{store.item.status_notes}}</pre>

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
                            :disabled="!store.assets.permissions.includes('can-update-module')"
                            @click="store.toEdit(store.item)"
                            data-testid="products-item-to-edit"
                            icon="pi pi-save"/>

                    <!--item_menu-->
                    <Button
                        type="button"
                        class="p-button-sm"
                        :disabled="!store.assets.permissions.includes('can-update-module')"
                        @click="toggleItemMenu"
                        data-testid="products-item-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="item_menu_state"
                          :model="store.item_menu_list"
                          :popup="true" />
                    <!--/item_menu-->

                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="products-item-to-list"
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
                                    data-testid="products-item-restore"
                                    @click="store.itemAction('restore')">
                            </Button>
                        </div>

                    </div>

                </Message>

                <div class="p-datatable p-component p-datatable-responsive-scroll p-datatable-striped p-datatable-sm">
                <table class="p-datatable-table">
                    <tbody class="p-datatable-tbody">
                    <template v-for="(value, column) in store.item ">

                        <template v-if="column === 'created_by'|| column === 'updated_by'|| column === 'all_variation'
                            || column === 'product_attributes'|| column === 'product_vendors'|| column === 'brand'
                            || column === 'store'|| column === 'type'|| column === 'status'||
                            column === 'product_variation'|| column === 'vendors' || column === 'meta' || column === 'deleted_by'
                            || column === 'status_notes' || column === 'vh_cms_content_form_field_id' || column === 'taxonomy_id_product_type'
                            || column === 'vh_st_store_id' || column === 'vh_st_brand_id'|| column === 'taxonomy_id_product_status' || column === 'details'
                            || column === 'quantity' || column === `seo_title` || column === `seo_meta_description` || column === `seo_meta_keyword`">
                        </template>

                        <template v-else-if="column === 'id' || column === 'uuid'">
                            <VhViewRow :label="column"
                                       :value="value"
                                       :can_copy="true"
                            />
                        </template>

                        <template v-else-if="column === 'name'">
                            <tr>
                                <td><b>Name</b></td>
                                <td  colspan="2" >
                                    <div class="word-overflow" style="width:300px;word-break: break-word;white-space: pre-wrap;">
                                        {{store.item.name}}</div>
                                </td>
                            </tr>
                        </template>

                        <template v-else-if="column === 'slug'">
                            <tr>
                                <td><b>Slug</b></td>
                                <td  colspan="2" >
                                    <div class="word-overflow" style="width:300px;word-break: break-word;white-space: pre-wrap;">
                                        {{store.item.slug}}</div>
                                </td>
                            </tr>
                            <tr v-if="store.item.type">
                                <td><b>Product Type</b></td>
                                <td  colspan="2" >
                                    <Button class="p-button-outlined p-button-secondary p-button-sm">
                                        {{store.item.type.name}}
                                    </Button>
                                </td>
                            </tr>

                            <tr v-if="store.item.store">
                                <td><b>Store</b></td>
                                <td  colspan="2" >
                                    <Button class="p-button-outlined p-button-secondary p-button-sm">
                                        {{store.item.store.name}}
                                    </Button>
                                </td>
                            </tr>

                            <tr v-if="store.item.brand">
                                <td><b>Brand</b></td>
                                <td  colspan="2" >
                                    <div class="word-overflow" style="width:300px;word-break: break-word;">
                                        {{store.item.brand.name}}</div>
                                </td>
                            </tr>

                            <tr>
                                <td><b>Status</b></td>
                                <td  colspan="2" >
                                    <Badge v-if="store.item.status.slug == 'approved'" severity="success">
                                        {{store.item.status.name}}
                                    </Badge>
                                    <Badge v-else-if="store.item.status.slug == 'pending'"  severity="warning">
                                        {{store.item.status.name}}
                                    </Badge>
                                    <Badge v-else-if="store.item.status.slug == 'rejected'" @click="vaah().copy(value.name)" severity="danger">
                                        {{store.item.status.name}}
                                    </Badge>
                                    <Badge v-else severity="primary">
                                        {{store.item.status.name}}
                                    </Badge>
                                </td>
                            </tr>

                        </template>

                        <template v-else-if="column === 'summary'">
                            <tr>
                                <td><b>Product Summary</b></td>
                                <td  colspan="2" >
                                    <pre v-html="store.item.summary" style="width:300px;word-break:break-word;white-space: pre-wrap;"/>
                                </td>
                            </tr>
                            <tr>
                                <td><b>Product Details</b></td>
                                <td colspan="2">
                                    <pre v-html="store.item.details" style="width:300px;word-break:break-word;white-space: pre-wrap;overflow-wrap:break-word;word-wrap:break-word;"/>
                                </td>
                            </tr>
                            <tr v-if="store.item.quantity > 0">
                                <td><b>Quantity</b></td>
                                <td colspan="2">
                                    {{ store.item.quantity }}
                                </td>
                            </tr>
                        </template>

                        <template v-else-if="column === 'seo_title'">
                            <tr v-if="store.item.seo_title">
                                <td><b>Seo Title</b></td>
                                <td >
                                    <Button icon="pi pi-eye"
                                            label="view"
                                            class="p-button-outlined p-button-secondary p-button-rounded p-button-sm"
                                            @click="store.openModal(value)"
                                            data-testid="display-seo-title"
                                    />
                                </td>
                            </tr>

                            <Dialog
                                    v-model:visible="store.display_seo_modal"
                                    :breakpoints="{'960px': '75vw', '640px': '90vw'}"
                                    :style="{width: '50vw'}" :modal="true"
                            >
                                <p class="m-0" v-html="'<pre>'+store.seo_meta_value+'<pre>'"></p>
                            </Dialog>

                        </template>

                        <template v-else-if="column === 'seo_meta_description'">
                            <tr v-if="store.item.seo_meta_description">
                                <td><b>Seo Meta Description</b></td>
                                <td >
                                    <Button icon="pi pi-eye"
                                            label="view"
                                            class="p-button-outlined p-button-secondary p-button-rounded p-button-sm"
                                            @click="store.openModal(value)"
                                            data-testid="display-seo-title"
                                    />
                                </td>
                            </tr>

                            <Dialog
                                v-model:visible="store.display_seo_modal"
                                :breakpoints="{'960px': '75vw', '640px': '90vw'}"
                                :style="{width: '50vw'}" :modal="true"
                            >
                                <p class="m-0" v-html="'<pre>'+store.seo_meta_value+'<pre>'"></p>
                            </Dialog>

                        </template>

                        <template v-else-if="column === 'seo_meta_keyword'">
                            <tr v-if="store.item.seo_meta_keyword">
                                <td><b>Seo Meta Keywords</b></td>
                                <td >
                                    <Button icon="pi pi-eye"
                                            label="view"
                                            class="p-button-outlined p-button-secondary p-button-rounded p-button-sm"
                                            @click="store.openModal(value)"
                                            data-testid="display-seo-title"
                                    />
                                </td>
                            </tr>

                            <Dialog
                                v-model:visible="store.display_seo_modal"
                                :breakpoints="{'960px': '75vw', '640px': '90vw'}"
                                :style="{width: '50vw'}" :modal="true"
                            >
                                <p class="m-0" v-html="'<pre>'+store.seo_meta_value+'<pre>'"></p>
                            </Dialog>

                        </template>

                        <template v-else-if="column === 'available_at'">
                            <tr v-if="store.item.available_at">
                                <td><b>Available From</b></td>
                                <td  colspan="2" >
                                  {{ store.item.available_at }}
                                </td>
                            </tr>
                        </template>

                        <template v-else-if="column === 'launch_at'">
                            <tr v-if="store.item.launch_at">
                                <td><b>Launch At</b></td>
                                <td  colspan="2" >
                                    {{ store.item.launch_at }}
                                </td>
                            </tr>
                        </template>

                        <template v-else-if="(column === 'created_by_user' || column === 'updated_by_user'  || column === 'deleted_by_user') && (typeof value === 'object' && value !== null)">
                            <VhViewRow :label="column"
                                       :value="value"
                                       type="user"
                            />
                        </template>

                        <template v-else-if="column === 'is_default'">
                            <VhViewRow :label="column"
                                       :value="value"
                                       type="yes-no"
                            />
                            <tr>
                                <td><b>Meta</b></td>
                                <td>
                                    <Button icon="pi pi-eye"
                                            label="view"
                                            class="p-button-outlined p-button-secondary p-button-rounded p-button-sm"
                                            @click="store.openModal()"
                                            :disabled="!store.item.seo_title &&
                                            !store.item.seo_meta_description &&
                                            !(store.item && store.item.seo_meta_keyword && Array.isArray(store.item.seo_meta_keyword) &&
                                             store.item.seo_meta_keyword.length > 0)"

                                            data-testid="meta-open_modal"
                                    />
                                </td>
                            </tr>

                        </template>

                        <template v-else-if="column === 'is_active'">
                            <VhViewRow :label="column"
                                       :value="value"
                                       type="yes-no"
                            />
                        </template>
                        <template v-else-if="column === 'is_featured_on_home_page'">
                            <VhViewRow :label="column"
                                       :value="value"
                                       type="yes-no"
                            />
                        </template>

                        <template v-else-if="column === 'is_featured_on_category_page'">
                            <VhViewRow :label="column"
                                       :value="value"
                                       type="yes-no"
                            />
                        </template>


                        <template v-else-if="column === 'in_stock'">
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
            v-model:visible="store.display_seo_modal"
            :breakpoints="{'960px': '75vw', '640px': '90vw'}"
            :style="{width: '50vw'}" :modal="true"
    >
        <div class="mb-4 flex"><span class="font-bold mr-2">Meta Title: </span><p>{{store.item.seo_title}}</p></div>
        <div class="mb-4 flex">
            <span class="font-bold mr-2" style="margin-top: 0.8rem;">Meta Description:</span>
            <pre style="font-family: Inter,ui-sans-serif">{{store.item.seo_meta_description}}</pre>
        </div>
        <div class="flex"><span class="font-bold mr-2">Meta Keywords: </span> <p>{{store.item.seo_meta_keyword}}</p></div>
    </Dialog>

</template>

