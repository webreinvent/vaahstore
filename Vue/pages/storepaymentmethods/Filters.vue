<script  setup>

import { useStorePaymentMethodStore } from '../../stores/store-storepaymentmethods'
import VhFieldVertical from './../../vaahvue/vue-three/primeflex/VhFieldVertical.vue'
import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import { useRootStore } from '@/stores/root'

const store = useStorePaymentMethodStore();
const root = useRootStore();

</script>

<template>
    <Panel :pt="root.panel_pt">
        <template class="p-1" #header>
            <b class="mr-1">Filters</b>
        </template>

        <template #icons>
            <Button data-testid="projects-hide-filter"
                    as="router-link"
                    :to="`/storepaymentmethods`"
                    icon="pi pi-times"
                    rounded
                    variant="text"
                    severity="contrast"
                    size="small">
            </Button>
        </template>

            <VhFieldVertical >
                <template #label>
                    <b>Status By:</b>
                </template>
                <VhField>
                    <MultiSelect
                        v-model="store.query.filter.payment_status"
                        :options="store.assets.taxonomy.status"
                        filter
                        optionValue="name"
                        optionLabel="name"
                        placeholder="Select Status"
                        display="chip"
                        class="w-full" />
                </VhField>


            </VhFieldVertical>

        <Divider/>



            <VhFieldVertical >
                <template #label>
                    <b>Payment Methods By:</b>
                </template>
                <VhField>
                    <MultiSelect
                        v-model="store.query.filter.payment_type"
                        :options="store.assets.active_payment_methods"
                        filter
                        optionValue="name"
                        optionLabel="name"
                        placeholder="Select Method"
                        display="chip"
                        class="w-full" />
                </VhField>


            </VhFieldVertical>

            <Divider/>

            <VhFieldVertical >
                <template #label>
                    <b>Sort By:</b>
                </template>

                <div class="field-radiobutton">
                    <RadioButton name="sort-none"
                                 inputId="sort-none"
                                 data-testid="storepaymentmethods-filters-sort-none"
                                 value=""
                                 v-model="store.query.filter.sort" />
                    <label for="sort-none" class="cursor-pointer">None</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="sort-ascending"
                                 inputId="sort-ascending"
                                 data-testid="storepaymentmethods-filters-sort-ascending"
                                 value="updated_at"
                                 v-model="store.query.filter.sort" />
                    <label for="sort-ascending" class="cursor-pointer">Updated (Ascending)</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="sort-descending"
                                 inputId="sort-descending"
                                 data-testid="storepaymentmethods-filters-sort-descending"
                                 value="updated_at:desc"
                                 v-model="store.query.filter.sort" />
                    <label for="sort-descending" class="cursor-pointer">Updated (Descending)</label>
                </div>

            </VhFieldVertical>

            <Divider/>

            <VhFieldVertical >
                <template #label>
                    <b>Is Active:</b>
                </template>

                <div class="field-radiobutton">
                    <RadioButton name="active-all"
                                 inputId="active-all"
                                 value="null"
                                 data-testid="storepaymentmethods-filters-active-all"
                                 v-model="store.query.filter.is_active" />
                    <label for="active-all" class="cursor-pointer">All</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="active-true"
                                 inputId="active-true"
                                 data-testid="storepaymentmethods-filters-active-true"
                                 value="true"
                                 v-model="store.query.filter.is_active" />
                    <label for="active-true" class="cursor-pointer">Only Active</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="active-false"
                                 inputId="active-false"
                                 data-testid="storepaymentmethods-filters-active-false"
                                 value="false"
                                 v-model="store.query.filter.is_active" />
                    <label for="active-false" class="cursor-pointer">Only Inactive</label>
                </div>

            </VhFieldVertical>

            <VhFieldVertical >
                <template #label>
                    <b>Trashed:</b>
                </template>

                <div class="field-radiobutton">
                    <RadioButton name="trashed-exclude"
                                 inputId="trashed-exclude"
                                 data-testid="storepaymentmethods-filters-trashed-exclude"
                                 value=""
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-exclude" class="cursor-pointer">Exclude Trashed</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="trashed-include"
                                 inputId="trashed-include"
                                 data-testid="storepaymentmethods-filters-trashed-include"
                                 value="include"
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-include" class="cursor-pointer">Include Trashed</label>
                </div>
                <div class="field-radiobutton">
                    <RadioButton name="trashed-only"
                                 inputId="trashed-only"
                                 data-testid="storepaymentmethods-filters-trashed-only"
                                 value="only"
                                 v-model="store.query.filter.trashed" />
                    <label for="trashed-only" class="cursor-pointer">Only Trashed</label>
                </div>

            </VhFieldVertical>


    </Panel>
</template>
