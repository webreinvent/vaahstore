<script setup>
import { vaah } from '../../../vaahvue/pinia/vaah'
import { useCartStore } from '../../../stores/store-carts'
import {useProductStore} from "../../../stores/store-products";

const store = useCartStore();
const product_store = useProductStore();
const useVaah = vaah();

</script>

<template>

    <div v-if="store.list">
        <!--table-->
         <DataTable :value="store.list.data"
                   dataKey="id"
                   :rowClass="store.setRowClass"
                   class="p-datatable-sm p-datatable-hoverable-rows"
                   :nullSortOrder="-1"
                   v-model:selection="store.action.items"
                   stripedRows
                   responsiveLayout="scroll">

            <Column selectionMode="multiple"
                    v-if="store.isListView()"
                    headerStyle="width: 3em">
            </Column>

            <Column field="id" header="ID" :style="{width: '80px'}" :sortable="true">
            </Column>

            <Column field="user" header="User Name"
                    class="overflow-wrap-anywhere"
                    :sortable="true">

                <template #body="prop">
                    <Badge v-if="prop.data.deleted_at"
                           value="Trashed"
                           severity="danger"></Badge>
                    <span v-if="!prop.data.user?.username">Guest User Cart</span>
                    <span v-else>{{ prop.data.user?.username }}</span>
                </template>

            </Column>

             <Column field="user" header="Email Address"
                     class="overflow-wrap-anywhere"
                     :sortable="true">
                 <template #body="prop">
                     <span v-if="!prop.data.user?.email">N/A</span>

                     <span v-else>
                         <Button class="p-button-tiny p-button-text p-0 mr-2"
                                 data-testid="taxonomies-table-to-edit"
                                 v-tooltip.top="'Copy Email'"
                                 @click="useVaah.copy(prop.data.user?.email)"
                                 icon="pi pi-copy"
                         />{{ prop.data.user?.email }}</span>
                 </template>

             </Column>

             <Column field="user" header="Phone Number"
                     class="overflow-wrap-anywhere"
                     :sortable="true">

                 <template #body="prop">
                     <span v-if="!prop.data.user?.phone">N/A</span>
                     <span v-else>
                        ({{ prop.data.user.country_calling_code }}) {{ prop.data.user.phone }}
                    </span>
                 </template>

             </Column>

             <Column field="products_count" header="Products Inside"
                     class="overflow-wrap-anywhere"
                     :sortable="true"></Column>

            <Column field="Actions" style="width:150px;"
                    :style="{width: store.getActionWidth() }"
                    header="Actions">

                <template #body="prop">
                    <div class="p-inputgroup ">
                        <Button
                            :disabled="prop.data.vh_user_id !== null && prop.data.user !== null"
                            class="p-button-tiny p-button-text"
                            data-testid="products-table-to-view"
                            v-tooltip.top="'Assign User'"
                            @click="store.openUserDialog(prop.data)"
                            icon="pi pi-user-plus"
                        />

                        <Button class="p-button-tiny p-button-text"
                                data-testid="carts-table-to-view"
                                v-tooltip.top="'Cart Details'"
                                @click="store.cartDetails(prop.data)"
                                icon="pi pi-fast-forward" />


                        <Button class="p-button-tiny p-button-success p-button-text"
                                data-testid="products-table-action-restore"
                                v-if="store.isListView() && prop.data.deleted_at"
                                @click="store.itemAction('restore', prop.data)"
                                v-tooltip.top="'Restore'"
                                icon="pi pi-replay" />
                    </div>

                </template>


            </Column>

             <template #empty>
                 <div class="text-center py-3">
                     No records found.
                 </div>
             </template>

        </DataTable>
        <!--/table-->

        <!--paginator-->
        <Paginator v-if="store.query.rows"
                   v-model:rows="store.query.rows"
                   :totalRecords="store.list.total"
                   :first="((store.query.page??1)-1)*store.query.rows"
                   @page="store.paginate($event)"
                   :rowsPerPageOptions="store.rows_per_page"
                   class="bg-white-alpha-0 pt-2">
        </Paginator>
        <!--/paginator-->

    </div>
    <Dialog v-model:visible="store.open_user_dialog" modal  position="top" header="Add User To Cart" :style="{ width: '30rem' }"
            @hide="store.onHideUserDialog">
        <div class="flex items-center gap-4 mb-4">
            <label for="username" class="font-semibold w-24 mt-2">User</label>
            <AutoComplete
                v-model="store.item.user_object"
                class="w-full"
                :suggestions="product_store.user_suggestions"
                @complete="product_store.searchUser($event)"
                placeholder="Search By Email or Phone"
                data-testid="carts-attach_user"
                name="carts-attach_user"
                optionLabel="email"

                :pt="{
                                              token: {
                        class: 'max-w-full'
                    },
                    removeTokenIcon: {
                    class: 'min-w-max'
                    },
                    item: { style: {
                    textWrap: 'wrap'
                    }  },
                    panel: { class: 'w-16rem ' }
                                                }">
            </AutoComplete>
        </div>

        <div class="flex justify-content-end gap-2">
            <Button type="button" label="Cancel" severity="secondary" @click="store.onHideUserDialog"></Button>
            <Button type="button" label="Add" @click="store.addUserToGuestCart(store.item.user_object)"></Button>
        </div>
    </Dialog>
</template>

