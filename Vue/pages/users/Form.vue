<script setup>
import {onMounted, ref, watch, watchEffect} from "vue";
import { useUserStore } from '../../stores/store-users'
import { vaah } from "../../vaahvue/pinia/vaah"
import FileUploader from "./components/FileUploader.vue";


import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';


const store = useUserStore();
const route = useRoute();
const useVaah = vaah();

onMounted(async () => {
    if (route.params && route.params.id) {
        await store.getItem(route.params.id);
    }

   await store.getFormMenu();

});



const myUploader = ref();

//--------form_menu
const form_menu = ref();
const toggleFormMenu = (event) => {
    form_menu.value.toggle(event);
};
//--------/form_menu

</script>
<template>
    <div class="col-5" >
        <Panel class="is-small">
            <template class="p-1" #header>
                <div class="flex flex-row">
                    <div class="p-panel-title">
                        <span v-if="store.item && store.item.id">
                            Update
                        </span>

                        <span v-else>
                            Create
                        </span>
                    </div>
                </div>
            </template>

            <template #icons>
                <div class="p-inputgroup">
                    <Button v-if="store.item && store.item.id"
                            label="Save"
                            class="p-button-sm"
                            @click="store.itemAction('save')"
                            data-testid="users-edit_save"
                            icon="pi pi-save"
                    />

                    <Button label="Create & New"
                            class="p-button-sm"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            data-testid="users-new_save"
                            icon="pi pi-save"
                            />
                    <Button data-testid="users-document" icon="pi pi-info-circle"
                            href="https://vaah.dev/store"
                            v-tooltip.top="'Documentation'"
                            onclick=" window.open('https://vaah.dev/store','_blank')"/>


                    <!--form_menu-->
                    <Button class="p-button-sm"
                            @click="toggleFormMenu"
                            icon="pi pi-angle-down"
                            aria-haspopup="true"
                            data-testid="users-form_menu"
                            />

                    <Menu ref="form_menu"
                          :model="store.form_menu_list"
                          :popup="true"
                    />

                    <Button class="p-button-sm"
                            icon="pi pi-times"
                            data-testid="users-list_view"
                            @click="store.toList()"
                    />
                </div>



            </template>


            <div v-if="store.item && store.assets" class="pt-2">

                <Message severity="error"
                         class="p-container-message mb-3"
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
                                    data-testid="articles-item-restore"
                                    @click="store.itemAction('restore')">
                            </Button>
                        </div>

                    </div>

                </Message>

                <div class="field mb-4 flex justify-content-between align-items-center"
                     v-if="store.item.id">

                    <img v-if="store.item.avatar"
                         :src="store.item.avatar"
                         alt=""
                         width="64"
                         height="64"
                         style="border-radius: 50%"
                    >

                    <div v-if="store.item.avatar_url">
                        <Button class="p-button-sm w-max"
                                data-testid="users-save"
                                @click="store.removeAvatar"
                                label="Remove"></Button>
                    </div>

                    <div class="w-max">
                        <FileUploader placeholder="Upload Avatar"
                                      :is_basic="true"
                                      data-testid="users-form_upload_avatar"
                                      :auto_upload="true"
                                      :uploadUrl="store.assets.upload_url" >
                        </FileUploader>
                    </div>

                </div>

                <VhField label="Email*">
                    <InputText :class="'w-full '+ store.email_error.class"
                               v-model="store.item.email"
                               @input="store.validateEmail"
                               name="users-email"
                               data-testid="users-email"
                               type="email"
                               aria-describedby="email-error"
                    />
                    <small id="email-error" class="p-error">{{ store.email_error.msg }}</small>
                </VhField>

                <VhField label="Username">
                    <InputText class="w-full"
                               v-model="store.item.username"
                               name="users-username"
                               data-testid="users-username"
                    />
                </VhField>

                <VhField label="Password">
                    <Password class="w-full"
                              v-model="store.item.password"
                              :feedback="false"
                              id="password"
                              name="users-password"
                              data-testid="users-password"
                              inputClass="w-full"
                              toggleMask
                    />
                </VhField>

                <VhField label="Display Name" v-if="!store.isHidden('display_name')">
                    <InputText class="w-full"
                               v-model="store.item.display_name"
                               name="users-display_name"
                               data-testid="users-display_name"
                    />
                </VhField>

                <template v-if="!store.isHidden('title')">
                    <VhField label="Title">
                        <Dropdown class="w-full"
                                  v-model="store.item.title"
                                  :options="store.assets.name_titles"
                                  optionLabel="name"
                                  optionValue="slug"
                                  id="Title"
                                  name="users-title"
                                  data-testid="users-title"
                        />
                    </VhField>
                </template>

                <VhField label="Designation" v-if="!store.isHidden('designation')">
                    <InputText class="w-full"
                               v-model="store.item.designation"
                               name="users-designation"
                               data-testid="users-designation"
                    />
                </VhField>

                <VhField label="First Name*">
                    <InputText class="w-full"
                               v-model="store.item.first_name"
                               name="users-first_name"
                               data-testid="users-first_name"
                    />
                </VhField>

                <VhField label="Middle Name" v-if="!store.isHidden('middle_name')">
                    <InputText class="w-full"
                               v-model="store.item.middle_name"
                               name="users-middle_name"
                               data-testid="users-middle_name"
                    />
                </VhField>

                <VhField label="Last Name" v-if="!store.isHidden('last_name')">
                    <InputText class="w-full"
                               v-model="store.item.last_name"
                               name="users-last_name"
                               data-testid="users-last_name"
                    />
                </VhField>

                <VhField label="Gender" v-if="!store.isHidden('gender')">
                    <SelectButton v-model="store.item.gender"
                                  :options="store.gender_options"
                                  optionLabel="label"
                                  optionValue="value"
                                  aria-labelledby="custom"
                                  name="users-gender"
                                  data-testid="users-gender"
                    >
                        <template #option="slotProps">
                            <p>{{ slotProps.option.label }}</p>
                        </template>
                    </SelectButton>
                </VhField>

                <VhField label="Country" v-if="!store.isHidden('country')">
                    <AutoComplete class="w-full"
                                  v-model="store.item.country"
                                  :suggestions="store.filtered_country_codes"
                                  @complete="store.searchCountryCode"
                                  @item-select="store.onSelectCountryCode"
                                  placeholder="Enter Your Country"
                                  optionLabel="name"
                                  name="users-country"
                                  data-testid="users-country"
                                  inputClass="w-full"
                    />
                </VhField>

                <VhField label="Country Code" v-if="!store.isHidden('country_calling_code')">
                    <Dropdown class="w-full"
                              v-model="store.item.country_calling_code"
                              :options="store.assets.countries"
                              :editable="true"
                              optionLabel="calling_code"
                              optionValue="calling_code"
                              id="calling_code"
                              name="users-country_calling_code"
                              data-testid="users-country_calling_code"
                    />
                </VhField>

                <VhField label="Phone" v-if="!store.isHidden('phone')">
                    <InputText class="w-full"
                               v-model="store.item.phone"
                               name="users-phone"
                               data-testid="users-phone"
                    />
                </VhField>

                <VhField label="bio" v-if="store.isHidden('bio')">
                    <Editor v-model="store.item.bio"
                            editorStyle="height: 320px"
                            name="users-bio"
                            data-testid="users-bio"
                    />
                </VhField>

                <VhField label="Website" v-if="!store.isHidden('website')">
                    <InputText class="w-full"
                               v-model="store.item.website"
                               name="users-website"
                               data-testid="users-website"
                    />
                </VhField>

                <VhField label="Timezone" v-if="!store.isHidden('timezone')">
                    <Dropdown v-model="store.item.timezone"
                              :options="store.assets.timezones"
                              optionLabel="name"
                              optionValue="slug"
                              :filter="true"
                              placeholder="Enter Your Timezone"
                              :showClear="true"
                              data-testid="users-timezone"
                              class="w-full"
                    />
                </VhField>

                <VhField label="Alternate Email" v-if="!store.isHidden('alternate_email')">
                    <InputText class="w-full"
                               v-model="store.item.alternate_email"
                               name="users-alternate_email"
                               data-testid="users-alternate_email"
                    />
                </VhField>

                <VhField label="Date of Birth" v-if="!store.isHidden('birth')">
                    <Calendar class="w-full"
                              id="dob"
                              inputId="basic"
                              v-model="store.item.birth"
                              autocomplete="off"
                              name="users-birth"
                              data-testid="users-birth"
                              dateFormat="dd-mm-yy"
                              :showTime="false"
                    />
                </VhField>

                <VhField label="Foreign User Id" v-if="!store.isHidden('foreign_user_id')">
                    <InputText class="w-full"
                               type="number"
                               v-model="store.item.foreign_user_id"
                               name="users-foreign_user_id"
                               data-testid="users-foreign_user_id"
                    />
                </VhField>


                <VhField label="Is Active*">
                    <InputSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 name="users-is_active"
                                 data-testid="users-is_active"
                                 v-model="store.item.is_active"/>
                </VhField>

                <template v-if="store.assets && store.assets.custom_fields"
                          v-for="(custom_field,key) in store.assets.custom_fields.value"
                          :key="key"
                >
                    <VhField :label="useVaah.toLabel(custom_field.name)"
                             v-if="!custom_field.is_hidden">
                        <Textarea v-if="custom_field.type === 'textarea'"
                                  class="w-full"
                                  rows="5"
                                  cols="30"
                                  :name=" 'users-meta_'+custom_field.slug"
                                  :data-testid="'users-meta_'+custom_field.slug"
                                  :min="custom_field.min"
                                  :max="custom_field.max"
                                  :minlength="custom_field.minlength"
                                  :maxlength="custom_field.maxlength"
                                  v-model="store.item.meta['custom_fields'][custom_field.slug]"
                        />

                        <Password v-else-if="custom_field.type === 'password'"
                                  :name=" 'users-meta_'+custom_field.slug"
                                  :data-testid="'users-meta_'+custom_field.slug"
                                  :min="custom_field.min"
                                  :max="custom_field.max"
                                  :minlength="custom_field.minlength"
                                  :maxlength="custom_field.maxlength"
                                  v-model="store.item.meta['custom_fields'][custom_field.slug]"
                                  toggleMask
                                  class="w-full"
                                  inputClass="w-full"
                        />

                        <InputText v-else
                                   class="w-full"
                                   :name=" 'users-meta_'+custom_field.slug"
                                   :data-testid="'users-meta_'+custom_field.slug"
                                   :type="custom_field.type"
                                   :min="custom_field.min"
                                   :max="custom_field.max"
                                   :minlength="custom_field.minlength"
                                   :maxlength="custom_field.maxlength"
                                   v-model="store.item.meta['custom_fields'][custom_field.slug]"
                        />
                    </VhField>
                </template>
            </div>
        </Panel>
    </div>
</template>
