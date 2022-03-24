<script src="./FormJs.js"></script>
<template>
    <div class="column" v-if="data && data.item">

        <div class="card">

            <!--header-->
            <header class="card-header">

                <div class="card-header-title">
                    {{data.form.type}}
                </div>


                <div class="card-header-buttons">

                    <div class="field has-addons is-pulled-right">
                        <p v-if="data.item && data.item.id" class="control">
                            <b-button @click="$vaah.copy(data.item.id)"  type="is-light">
                                <small><b>#{{data.item.id}}</b></small>
                            </b-button>
                        </p>
                        <p class="control">
                            <b-button v-if="data.form.type ==='Update'"
                                      icon-left="edit"
                                      type="is-light"
                                      :loading="data.form.is_button_loading"
                                      @click="setFormAction('save')">
                                Save
                            </b-button>

                            <b-button v-else
                                      icon-left="edit"
                                      type="is-light"
                                      :loading="data.form.is_button_loading"
                                      @click="setFormAction('save-and-new')">
                                Save & New
                            </b-button>
                        </p>

                        <p class="control">


                            <b-dropdown aria-role="list" position="is-bottom-left">
                                <button class="button is-light" slot="trigger">
                                    <b-icon icon="caret-down"></b-icon>
                                </button>

                                <b-dropdown-item v-if="data.form.type === 'Create'"
                                                 @click="setFormAction('save-and-close')">
                                    <b-icon icon="check"></b-icon>
                                    Save & Close
                                </b-dropdown-item>

                                <b-dropdown-item v-if="data.form.type === 'Create'"
                                                 @click="setFormAction('save-and-clone')">
                                    <b-icon icon="copy"></b-icon>
                                    Save & Clone
                                </b-dropdown-item>

                                <b-dropdown-item v-if="data.form.type === 'Create'"
                                                 @click="resetNewItem()">
                                    <b-icon icon="eraser"></b-icon>
                                    Reset
                                </b-dropdown-item>

                                <b-dropdown-item @click="getFaker()">
                                    <b-icon icon="i-cursor"></b-icon>
                                    Fill Dummy Data
                                </b-dropdown-item>

                            </b-dropdown>


                        </p>


                        <p class="control" v-if="data.item.id">
                            <b-button type="is-light"
                                      @click="backToView()"
                                      icon-left="arrow-left">
                            </b-button>
                        </p>


                        <p class="control">
                            <b-button type="is-light"
                                      @click="closeCard()"
                                      icon-left="times">
                            </b-button>
                        </p>



                    </div>


                </div>

            </header>
            <!--/header-->

            <!--content-->
            <div class="card-content">

                <b-field label="Name" :label-position="data.form.label_position">
                    <b-input name="vendors-name"
                             data-wdio="vendors-name"
                             v-model="data.item.name"></b-input>
                </b-field>

                <b-field label="Slug" :label-position="data.form.label_position">
                    <b-input name="vendors-slug"
                             data-wdio="vendors-slug"
                             v-model="data.item.slug">
                    </b-input>
                </b-field>
            </div>
            <!--/content-->





        </div>




    </div>
</template>


