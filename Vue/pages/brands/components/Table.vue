<script setup>
import { vaah } from '../../../vaahvue/pinia/vaah'
import { useBrandStore } from '../../../stores/store-brands'

const store = useBrandStore();
const useVaah = vaah();
</script>

<template>

    <div v-if="store.list">
        <!--table-->
        <DataTable :value="store.list.data"
                   dataKey="id"
                   class="p-datatable-sm p-datatable-hoverable-rows"
                   :rowClass="(rowData) =>rowData.id === store.item?.id ? 'bg-yellow-100':''"
                   v-model:selection="store.action.items"
                   stripedRows
                   responsiveLayout="scroll">

            <Column selectionMode="multiple"
                    v-if="store.isListView()"
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
                    <div class="name-cell">
                        {{ prop.data.name }}
                    </div>
                </template>

            </Column>

            <Column field="products" header="Store"
                    :sortable="false">

                <template #body="prop">

                    <Button :disabled="store.countStore(prop.data.products) < 1"
                            @click="store.storeIds(prop.data.products)">{{(store.countStore((prop.data.products)))}}
                    </Button>
                </template>

            </Column>

            <Column field="registered_by_user.first_name" header="Registered By"
                    :sortable="true">
                <template #body="prop">
                    <div v-if="prop.data.registered_by_user">
                        {{prop.data.registered_by_user.first_name}}
                    </div>
                </template>
            </Column>

            <Column field="approved_by_user.first_name" header="Approved By"
                    :sortable="true">
                <template #body="prop">
                    <div v-if="prop.data.approved_by_user">
                        {{prop.data.approved_by_user.first_name}}
                    </div>

                </template>
            </Column>


            <Column field="status.name" header="Status"
                    :sortable="true">
                <template #body="prop">

                    <Badge v-if="prop.data.status && prop.data.status.slug == 'approved'"
                           severity="success"> {{prop.data.status.name}} </Badge>
                    <Badge v-else-if="prop.data.status && prop.data.status.slug == 'rejected'"
                           severity="danger"> {{prop.data.status.name}} </Badge>
                    <Badge v-else-if="prop.data.status && prop.data.status.slug == 'pending'"
                           severity="warn"> {{prop.data.status.name}} </Badge>
                    <span v-else>

                     </span>
                </template>
            </Column>

            <Column field="updated_at" header="Updated"
                    v-if="store.isListView()"
                    style="width:150px;"
                    :sortable="true">

                <template #body="prop">
                    {{useVaah.toLocalTimeShortFormat(prop.data.updated_at)}}
                </template>

            </Column>

            <Column field="is_active" v-if="store.isListView()"
                    style="width:100px;"
                    header="Is Active">

                <template #body="prop">
                    <ToggleSwitch v-model.bool="prop.data.is_active"
                                 data-testid="brands-table-is-active"
                                 v-bind:false-value="0"  v-bind:true-value="1"
                                 class="p-inputswitch-sm"
                                 :pt="{
                                        slider: ({ props }) => ({
                                            class: props.modelValue ? 'bg-green-600' : ' '
                                        })
                                 }"
                                 :disabled="!store.assets.permissions.includes('can-update-module')"
                                 @input="store.toggleIsActive(prop.data)">
                    </ToggleSwitch>
                </template>

            </Column>

            <Column field="actions" style="width:150px;"
                    :style="{width: store.getActionWidth() }"
                    :header="store.getActionLabel()">

                <template #body="prop">
                    <div class="p-inputgroup ">

                        <Button class="p-button-tiny p-button-text"
                                data-testid="brands-table-to-view"
                                v-tooltip.top="'View'"
                                :disabled="$route.path.includes('view') && prop.data.id===store.item.id"
                                @click="store.toView(prop.data)"
                                icon="pi pi-eye" />

                        <Button v-if=" store.assets.permissions.includes('can-update-module') "
                                class="p-button-tiny p-button-text"
                                data-testid="brands-table-to-edit"
                                v-tooltip.top="'Update'"
                                :disabled="$route.path.includes('form') && store.item && prop.data.id===store.item.id"
                                @click="store.toEdit(prop.data)"
                                icon="pi pi-pencil" />

                        <Button class="p-button-tiny p-button-danger p-button-text"
                                data-testid="brands-table-action-trash"
                                v-if="store.isListView() && !prop.data.deleted_at &&
                                store.assets.permissions.includes('can-update-module')"
                                @click="store.itemAction('trash', prop.data)"
                                v-tooltip.top="'Trash'"
                                icon="pi pi-trash" />


                        <Button class="p-button-tiny p-button-success p-button-text"
                                data-testid="brands-table-action-restore"
                                v-if="store.isListView() && prop.data.deleted_at &&
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

        <Divider />

        <!--paginator-->
        <Paginator v-model:rows="store.query.rows"
                   :totalRecords="store.list.total"
                   :first="(store.query.page-1)*store.query.rows"
                   @page="store.paginate($event)"
                   :rowsPerPageOptions="store.rows_per_page"
                   class="bg-white-alpha-0 pt-2">
        </Paginator>
        <!--/paginator-->

    </div>

</template>

<style scoped>

.name-cell{
    min-width: 150px;
}


</style>
