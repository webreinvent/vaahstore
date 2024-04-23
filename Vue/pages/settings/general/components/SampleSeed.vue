<template>

<!--    <div class="card flex justify-content-center">
        <Dropdown class="w-full md:w-14rem mr-1"
                  v-model="store.selected_crud"
                  :options="store.crud_options"
                  optionLabel="label"
                  optionValue="value"
                  name="sample-crud_options"
                  placeholder="Select Crud"
                  data-testid="sample-crud_options"
        />
        <InputNumber id="input_text" v-model="store.quantity" placeholder="Enter no. of records" class="mr-1" />

        <Button label="Submit" @click="store.createBulkRecords()" :disabled="store.is_button_disabled"   />
    </div>
    -->


    <Button type="button" label="Fill All Inputs" icon="pi pi-arrow-down" @click="store.fillAll()" style="margin-left: 50rem"/>

    <Button type="button" label="Reset All Inputs " icon="pi pi-arrow-down" @click="store.resetAll()"/>

    <div class="container">

        <div class="card  justify-content-center gap-3 mt-5">

            <div class="flex align-items-center">
                <Checkbox v-model="pizza" inputId="ingredient1" name="pizza" value="Cheese" />
                <label  class="ml-2"> All </label>
                <InputNumber placeholder="Enter quantity" v-model="store.input_number_value" style="margin-left: 8.6rem" />
            </div>

        <div class="flex align-items-center">
            <Checkbox v-model="pizza" inputId="ingredient1" name="pizza" value="Cheese" />
            <label  class="ml-2"> Store  </label>
            <InputNumber placeholder="Enter quantity" v-model="store.input_number_value" class="mt-2"  style="margin-left: 7.5rem"/>
        </div>


        <div class="flex align-items-center ">
            <Checkbox v-model="pizza" inputId="ingredient1" name="pizza" value="Cheese" />
            <label  class="ml-2"> Store Payment Methods </label>
            <InputNumber placeholder="Enter quantity" class="ml-2 mt-2" v-model="store.input_number_value" />
        </div>

        <div class="flex align-items-center">
            <Checkbox v-model="pizza" inputId="ingredient1" name="pizza" value="Cheese" />
            <label  class="ml-2"> Vendor </label>
            <InputNumber placeholder="Enter quantity" v-model="store.input_number_value" class="mt-2" style="margin-left: 6.9rem" />
        </div>

        <div class="flex align-items-center">
            <Checkbox v-model="pizza" inputId="ingredient1" name="pizza" value="Cheese" />
            <label  class="ml-2"> Vendor Products </label>
            <InputNumber placeholder="Enter quantity" v-model="store.input_number_value" class=" mt-2" style="margin-left: 3.2rem" />
        </div>


        <div class="flex align-items-center">
            <Checkbox v-model="pizza" inputId="ingredient1" name="pizza" value="Cheese" />
            <label  class="ml-2"> Products </label>
            <InputNumber placeholder="Enter quantity" v-model="store.input_number_value" class="mt-2" style="margin-left: 6rem" />
        </div>

        <div class="flex align-items-center">
            <Checkbox v-model="pizza" inputId="ingredient1" name="pizza" value="Cheese" />
            <label  class="ml-2"> Product Variations </label>
            <InputNumber placeholder="Enter quantity" v-model="store.input_number_value" class=" mt-2" style="margin-left: 2.4rem" />
        </div>

        <div class="flex align-items-center">
            <Checkbox v-model="pizza" inputId="ingredient1" name="pizza" value="Cheese" />
            <label  class="ml-2"> Product Attributes </label>
            <InputNumber placeholder="Enter quantity" v-model="store.input_number_value" class=" mt-2" style="margin-left: 2.4rem" />
        </div>

        <div class="flex align-items-center">
            <Checkbox v-model="pizza" inputId="ingredient1" name="pizza" value="Cheese" />
            <label  class="ml-2"> Product Medias </label>
            <InputNumber placeholder="Enter quantity" v-model="store.input_number_value" class="mt-2" style="margin-left: 3.4rem" />
        </div>


        <div class="flex align-items-center">
            <Checkbox v-model="pizza" inputId="ingredient1" name="pizza" value="Cheese" />
            <label  class="ml-2"> Product Stocks </label>
            <InputNumber placeholder="Enter quantity" v-model="store.input_number_value" class="mt-2" style="margin-left: 3.4rem" />
        </div>



    </div>

    <div class="card justify-content-center gap-3 mt-5">

        <div class="flex align-items-center">
            <Checkbox v-model="pizza" inputId="ingredient1" name="pizza" value="Cheese" />
            <label  class="ml-2"> Brands </label>
            <InputNumber placeholder="Enter quantity" v-model="store.input_number_value" class="mt-2" style="margin-left: 6.6rem" />
        </div>

        <div class="flex align-items-center">
            <Checkbox v-model="pizza" inputId="ingredient1" name="pizza" value="Cheese" />
            <label  class="ml-2"> WareHouse </label>
            <InputNumber placeholder="Enter quantity" v-model="store.input_number_value" class="mt-2" style="margin-left: 5rem" />
        </div>

        <div class="flex align-items-center">
            <Checkbox v-model="pizza" inputId="ingredient1" name="pizza" value="Cheese" />
            <label  class="ml-2"> Attributes </label>
            <InputNumber placeholder="Enter quantity"  v-model="store.input_number_value" class=" mt-2" style="margin-left: 5.6rem" />
        </div>

        <div class="flex align-items-center">
            <Checkbox v-model="pizza" inputId="ingredient1" name="pizza" value="Cheese" />
            <label  class="ml-2"> Attributes Group  </label>
            <InputNumber placeholder="Enter quantity" v-model="store.input_number_value" class=" mt-2" style="margin-left: 3rem" />
        </div>

        <div class="flex align-items-center">
            <Checkbox v-model="pizza" inputId="ingredient1" name="pizza" value="Cheese" />
            <label  class="ml-2"> Orders </label>
            <InputNumber placeholder="Enter quantity" v-model="store.input_number_value" class="mt-2" style="margin-left: 6.6rem" />
        </div>

        <div class="flex align-items-center">
            <Checkbox v-model="pizza" inputId="ingredient1" name="pizza" value="Cheese" />
            <label  class="ml-2"> Payments Methods </label>
            <InputNumber placeholder="Enter quantity" v-model="store.input_number_value" class="mt-2" style="margin-left: 2rem" />
        </div>

        <div class="flex align-items-center">
            <Checkbox v-model="pizza" inputId="ingredient1" name="pizza" value="Cheese" />
            <label  class="ml-2"> Addresses </label>
            <InputNumber placeholder="Enter quantity" v-model="store.input_number_value" class="mt-2" style="margin-left: 5.2rem" />
        </div>

        <div class="flex align-items-center">
            <Checkbox v-model="pizza" inputId="ingredient1" name="pizza" value="Cheese" />
            <label  class="ml-2"> Wishlists </label>
            <InputNumber placeholder="Enter quantity" v-model="store.input_number_value" class=" mt-2" style="margin-left: 5.8rem" />
        </div>

        <div class="flex align-items-center">
            <Checkbox v-model="pizza" inputId="ingredient1" name="pizza" value="Cheese" />
            <label  class="ml-2"> Customers </label>
            <InputNumber placeholder="Enter quantity" v-model="store.input_number_value" class="mt-2" style="margin-left: 5rem" />
        </div>

        <div class="flex align-items-center">
            <Checkbox v-model="pizza" inputId="ingredient1" name="pizza" value="Cheese" />
            <label  class="ml-2"> Customers Groups </label>
            <InputNumber placeholder="Enter quantity" v-model="store.input_number_value" class="mt-2" style="margin-left: 2.1rem" />
        </div>
    </div>

    </div>
    <Button label="Submit" style="width: 10rem; margin-top: 1.5rem; border-radius: 4rem" />


</template>

<script setup>
import {useSettingStore} from '../../../../stores/store-settings'
import {useRoute} from "vue-router";
import {onMounted , ref} from "vue";

const store = useSettingStore();

const route = useRoute();

onMounted(async () => {

    store.is_button_disabled = false;
    store.quantity = null;
    store.selected_crud = null;
});

const pizza = ref()



</script>

<style scoped>

.container {
    display: flex;
    justify-content: space-between;
}

</style>
