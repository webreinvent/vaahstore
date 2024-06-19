<script setup>
import {onMounted, ref, watch} from "vue";
import {useRoute} from 'vue-router';

import { usePaymentStore } from '../../stores/store-payments'

import VhViewRow from '../../vaahvue/vue-three/primeflex/VhViewRow.vue';
const store = usePaymentStore();
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

});

//--------toggle item menu
const item_menu_state = ref();
const toggleItemMenu = (event) => {
    item_menu_state.value.toggle(event);
};
//--------/toggle item menu

</script>
<template>

    <div class="col-8" >

        <Panel class="is-small" v-if="store && store.item">

            <template class="p-1" #header>

                <div class="p-panel-title w-7 white-space-nowrap
                overflow-hidden text-overflow-ellipsis">
                    #{{store.item.id}} {{'Order Payment Details'}}
                </div>

            </template>

            <template #icons>


                <div class="p-inputgroup">

                    <Button label="Edit"
                            class="p-button-sm"
                            @click="store.toEdit(store.item)"
                            data-testid="payments-item-to-edit"
                            icon="pi pi-save"/>

                    <!--item_menu-->
                    <Button
                        type="button"
                        class="p-button-sm"
                        @click="toggleItemMenu"
                        data-testid="payments-item-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="item_menu_state"
                          :model="store.item_menu_list"
                          :popup="true" />
                    <!--/item_menu-->

                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="payments-item-to-list"
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
                                    data-testid="payments-item-restore"
                                    @click="store.itemAction('restore')">
                            </Button>
                        </div>

                    </div>

                </Message>
                <div class="mt-4" v-if="store.item && store.item.orders">
                <DataTable :value="store.item.orders"
                           dataKey="id" showGridlines
                           :rows="10"
                           :paginator="true"
                           class="p-datatable-sm p-datatable-hoverable-rows"
                           :nullSortOrder="-1"
                           v-model:selection="store.action.items"
                           responsiveLayout="scroll">



                    <Column field="id" header="Order ID" :style="{width: '80px'}" :sortable="true">{{'1'}}
                    </Column>
                    <Column  header="Order"
                            class="overflow-wrap-anywhere"
                            :sortable="true">
                        <template #body="prop">
                            {{prop.data.user.name}}

                        </template>


                    </Column>
                    <Column  header="Payable"
                             class="overflow-wrap-anywhere"
                            :sortable="true">

                        <template #body="prop">
                            <Badge severity="info">{{prop.data.payable}}</Badge>

                        </template>

                    </Column>
                    <Column  header="Paid"  class="overflow-wrap-anywhere"
                             :sortable="true">
                        <template #body="prop">
                            <Badge severity="primary">{{prop.data.paid}}</Badge>
                        </template>

                    </Column>
                    <Column  header="Payment Amount"  class="overflow-wrap-anywhere"
                             :sortable="true">

                        <template #body="prop">
                            <Badge v-if="prop.data.pivot.paid == 0"
                                   value="0"
                                   severity="danger"></Badge>
                            <Badge v-else-if="prop.data.pivot.paid > 0"
                                   :value="prop.data.pivot.paid"
                                   severity="secondary"></Badge>
                        </template>

                    </Column>

                    <Column field="paymentStatus"  header="Payment Status"  class="overflow-wrap-anywhere"
                            :sortable="true">
                        <template #body="prop">
                            <Badge v-if="prop.data.payable === prop.data.paid"
                                   severity="success"> {{'Paid'}} </Badge>
                            <Badge v-else-if="prop.data.payable > prop.data.paid"
                                   severity="info"> {{'Partially Paid'}} </Badge>
                            <Badge v-else
                                   severity="warning"> Pending </Badge>




<!--                            <Badge v-if="prop.data.paymentStatus == 'Paid'"-->
<!--                                   severity="success"> {{prop.data.paymentStatus}} </Badge>-->
<!--                            <Badge v-else-if="prop.data.paymentStatus == 'Pending'"-->
<!--                                   severity="danger"> {{prop.data.paymentStatus}} </Badge>-->
<!--                            <Badge v-else-->
<!--                                   severity="warning"> {{prop.data.paymentStatus}} </Badge>-->
                        </template>


                    </Column>

<!--                    <Column field="updated_at" header="Updated"-->
<!--                            -->
<!--                            style="width:150px;"-->
<!--                            :sortable="true">-->

<!--                        <template #body="prop">-->
<!--                            {{useVaah.toLocalTimeShortFormat(prop.data.updated_at)}}-->
<!--                        </template>-->

<!--                    </Column>-->

<!--                    <Column field="is_active"-->
<!--                            :sortable="true"-->
<!--                            style="width:100px;"-->
<!--                            header="Is Active">-->

<!--                        <template #body="prop">-->
<!--                            <InputSwitch v-model.bool="prop.data.is_active"-->
<!--                                         data-testid="payments-table-is-active"-->
<!--                                         v-bind:false-value="0"  v-bind:true-value="1"-->
<!--                                         class="p-inputswitch-sm"-->
<!--                                         @input="store.toggleIsActive(prop.data)">-->
<!--                            </InputSwitch>-->
<!--                        </template>-->

<!--                    </Column>-->



                    <template #empty>
                        <div class="text-center py-3">
                            No records found.
                        </div>
                    </template>

                </DataTable>
                </div>
            </div>
        </Panel>

    </div>

</template>
