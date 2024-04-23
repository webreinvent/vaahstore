<script setup>
import { vaah } from '../../../vaahvue/pinia/vaah'
import { useProductStore } from '../../../stores/store-products'
import ProductCategories from '../components/ProductCategories.vue'
import { useDialog } from "primevue/usedialog";
import {ref} from "vue";
const store = useProductStore();
const useVaah = vaah()
const visible = ref(false);

const dialog = useDialog();
const openProductCategories = (categories) => {
    const dialogRef = dialog.open(ProductCategories, {
        props: {
            header: 'Categories',
            style: {
                width: '50vw',
            },
            breakpoints:{
                '960px': '75vw',
                '640px': '90vw'
            },
            modal: true
        },
        data : {'categories' : categories},
    });
}

</script>

<template>

    <div v-if="store.list" class="data-container" style=" display: flex;flex-direction: column;justify-content: center; height: 100%;">
        <!--table-->
         <DataTable :value="store.list.data"
                       dataKey="id"
                    :rowClass="(rowData) => rowData.id === store.item?.id ? 'bg-yellow-100' : ''"

                   class="p-datatable-sm p-datatable-hoverable-rows"
                   v-model:selection="store.action.items"
                   stripedRows
                   responsiveLayout="scroll">

            <Column selectionMode="multiple"
                    v-if="store.isViewLarge()"
                    headerStyle="width: 3em">
            </Column>

            <Column field="id" header="ID" :style="{width: store.getIdWidth()}" :sortable="true">
            </Column>

            <Column field="name" header="Name"
                    :sortable="true">

                <template #body="prop">
                    <Badge v-if="prop.data.deleted_at"
                           value="Trashed"
                           severity="danger"></Badge>
                    <span v-if="prop.data.is_default">
                        <div style="word-break: break-word;">{{ prop.data.name }}</div>
                         </span>
                    <span v-else>
                        <div style="word-break: break-word;">{{ prop.data.name }}</div>
                    </span>
                </template>

            </Column>

             <Column field="store.name" header="Store"
                     :sortable="true">

                 <template #body="prop">
                     <Badge v-if="prop.data && prop.data.store && prop.data.store.deleted_at"
                            value="Trashed"
                            severity="danger"></Badge>
                     <span>
                        <div style="word-break: break-word;" v-if="prop.data && prop.data.store">
                            {{ prop.data.store.name }}</div>
                         </span>
                 </template>

             </Column>


             <Column field="quantity" header="Quantity"
                     v-if="store.isViewLarge()"
                     :sortable="true">

                 <template #body="prop">
                     <Badge v-if="prop.data.quantity == 0 || prop.data.quantity === null"
                            value="0"
                            severity="danger"></Badge>
                     <Badge v-else-if="prop.data.quantity > 0"
                            :value="prop.data.quantity"
                            severity="info"></Badge>
                 </template>
             </Column>

             <Column field="variations" header="Variations"
                     :sortable="false">

                 <template #body="prop">
                     <div class="p-inputgroup">
                         <span  v-if="prop.data.product_variations && prop.data.product_variations.length"
                                class="p-inputgroup-addon cursor-pointer"
                                v-tooltip.top="'View Variations'"
                                @click="store.toViewVariation(prop.data)">

                             <b>{{prop.data.product_variations.length}}</b>

                         </span>
                         <span class="p-inputgroup-addon" v-else>
                             <b>{{prop.data.product_variations.length}}</b>
                         </span>
                         <Button icon="pi pi-plus" severity="info" v-if="!prop.data.deleted_at"
                                 v-tooltip.top="'Add Variations'"
                                 :disabled="prop.data.id===store.item?.id && $route.path.includes('variation')"
                                 @click="store.toVariation(prop.data)" />
                     </div>

                 </template>
             </Column>

             <Column field="vendors" header="Vendors"
                     :sortable="false">
                 <template #body="prop">
                     <div class="p-inputgroup">
                         <span class="p-inputgroup-addon cursor-pointer"
                               v-tooltip.top="'View Vendors'"
                               v-if="prop.data.product_vendors && prop.data.product_vendors.length"
                               @click="store.toViewVendors(prop.data)">
                             <b >{{prop.data.product_vendors.length}}</b>

                         </span>
                         <span class="p-inputgroup-addon"
                               v-else>
                             <b >{{prop.data.product_vendors.length}}</b>

                         </span>
                         <Button icon="pi pi-plus" severity="info" v-if="!prop.data.deleted_at"
                                 v-tooltip.top="'Add Vendors'"
                                 :disabled="prop.data.id===store.item?.id  && $route.path.includes('vendor')"
                                 @click="store.toVendor(prop.data)" />

                     </div>
                 </template>


             </Column>


             <Column field="categories.name" header="Category" :sortable="true">
                 <template #body="prop">
                     <div class="flex flex-wrap gap-2" v-if="prop.data.categories && prop.data.categories.length">
                         <template v-if="prop.data.categories.some(category => category.deleted_at === null)">
<!--                             <Button label="View" @click="visible = true" />-->
                             <Button class="p-button-tiny p-button-text"
                                     data-testid="taxonomies-table-to-manage-taxonomy-type-modal"

                                     icon="pi pi-pencil"
                                     @click="openProductCategories(prop.data.categories)"
                             />
                             <template v-for="(category, index) in prop.data.categories" :key="index">
                                 <div>

                        <span v-if="index === 0 && category.deleted_at === null" class="h-max max-w-full ">
                            {{ category.name }}
                        </span>
                                     <span v-tooltip.top="store.getTooltipText(prop.data.categories)" v-if="index === 0 && category.deleted_at === null && prop.data.categories.length > 1" class="cursor-pointer ml-1 text-blue-500">
                            +{{ prop.data.categories.filter(category => category.deleted_at === null).length - 1 }} more
                        </span>
                                 </div>
                             </template>
                         </template>
                         <template v-else>
                             <Badge value="Trashed" severity="danger"></Badge>
                         </template>
                     </div>
                 </template>
             </Column>





             <Column field="status.name" header="Status"
                     :sortable="true">

                 <template #body="prop">

                     <Badge v-if="prop.data.status.slug == 'approved'"
                            severity="success"> {{prop.data.status.name}} </Badge>
                     <Badge v-else-if="prop.data.status.slug == 'rejected'"
                            severity="danger"> {{prop.data.status.name}} </Badge>
                     <Badge v-else
                            severity="warning"> {{prop.data.status.name}} </Badge>
                 </template>

             </Column>

            <Column field="is_active" v-if="store.isViewLarge()"
                    style="width:80px;"
                    header="Is Active">

                <template #body="prop">
                    <InputSwitch v-model.bool="prop.data.is_active"
                                 :disabled="!store.assets.permissions.includes('can-update-module')"
                                 data-testid="products-table-is-active"
                                 v-bind:false-value="0"  v-bind:true-value="1"
                                 class="p-inputswitch-sm"
                                 @input="store.toggleIsActive(prop.data)">
                    </InputSwitch>
                </template>

            </Column>

            <Column field="actions"
                    :style="{width: store.getActionWidth() }"
                    :header="store.getActionLabel()">

                <template #body="prop">
                    <div class="p-inputgroup ">

                        <Button class="p-button-tiny p-button-text"
                                data-testid="products-table-to-view"
                                :disabled="$route.path.includes('view') && prop.data.id===store.item?.id"
                                v-tooltip.top="'View'"
                                @click="store.toView(prop.data)"
                                icon="pi pi-eye" />

                        <Button v-if=" store.assets.permissions.includes('can-update-module') "
                                class="p-button-tiny p-button-text"
                                data-testid="products-table-to-edit"
                                :disabled="$route.path.includes('form') && prop.data.id===store.item?.id"
                                v-tooltip.top="'Update'"
                                @click="store.toEdit(prop.data)"
                                icon="pi pi-pencil" />

                        <Button class="p-button-tiny p-button-danger p-button-text"
                                data-testid="products-table-action-trash"
                                v-if="store.isViewLarge() && !prop.data.deleted_at &&
                                store.assets.permissions.includes('can-update-module')"
                                @click="store.itemAction('trash', prop.data)"
                                v-tooltip.top="'Trash'"
                                icon="pi pi-trash" />


                        <Button class="p-button-tiny p-button-success p-button-text"
                                data-testid="products-table-action-restore"
                                v-if="store.isViewLarge() && prop.data.deleted_at &&
                                 store.assets.permissions.includes('can-update-module')"
                                @click="store.itemAction('restore', prop.data)"
                                v-tooltip.top="'Restore'"
                                icon="pi pi-replay" />

                    </div>

                </template>


            </Column>

             <template #empty="prop">

                 <div class="no-record-message" style="text-align: center;font-size: 12px; color: #888;">No records found.</div>

             </template>


        </DataTable>
        <!--/table-->

        <!--paginator-->
        <Paginator v-model:rows="store.query.rows"
                   :totalRecords="store.list.total"
                   @page="store.paginate($event)"
                   :rowsPerPageOptions="store.rows_per_page"
                   class="bg-white-alpha-0 pt-2">
        </Paginator>
        <!--/paginator-->
        <DynamicDialog  />

        <Dialog v-model:visible="visible" modal header="Credit Card Information" class="rounded-dialog"  :breakpoints="{'960px': '75vw', '640px': '90vw'}" :style="{width: '30vw'}">
            <hr>
            <span class="p-text-secondary block mt-4 mb-5">We hope you are doing well. Please make the payment using the following URL:
    <a href="https://www.simoc.com/solos-auto-repair/avcst125xfrssssdhfkjndsfsuwieqw4567dsfsd">https://www.simoc.com/solos-auto-repair/avcst125xfrssssdhfkjndsfsuwieqw4567dsfsd</a>
</span>

            <table style="border-collapse: collapse; width: 100%;">
                <tr  style="border: 1px solid #ccc;">
                    <td style="padding: 8px; border-right: 1px solid #ddd;">Card No</td>
                    <!--                <td style="padding: 8px;">{{store.item.categories}}</td>-->

                    <td v-for="(category, index) in store.item.categories" :key="category.id">
                                             <span v-if="category.deleted_at === null" class="h-max max-w-full ">
                            {{ category.name }}
                        </span>
                    </td>
                </tr>
                <tr  style="border: 1px solid #ccc;">
                    <td style="padding: 8px; border-right: 1px solid #ddd;">CVV</td>
                    <td style="padding: 8px;">{{store.item.cvv}}</td>
                </tr>
                <tr  style="border: 1px solid #ccc;">
                    <td style="padding: 8px; border-right: 1px solid #ddd;">Expires On</td>
                    <td style="padding: 8px;">{{store.item.expires_year}}/{{store.item.expires_month}}</td>
                </tr>
            </table>
            <div class="mt-5" style="display: flex; justify-content: space-between;">
                <Button type="button" label="Cancel" severity="secondary" @click="visible = false"></Button>
                <Button type="button" label="Send Mail" class="p-button-primary" @click="sendMail"></Button>
            </div>

        </Dialog>
    </div>

</template>
