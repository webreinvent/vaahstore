<script src="./CreateJs.js"></script>
<template>
    <div class="column" v-if="data && data.item">

        <div class="card">

            <!--header-->
            <header class="card-header">

                <div class="card-header-title">
                    Create
                </div>


                <div class="card-header-buttons">

                    <div class="field has-addons is-pulled-right">
                        <p class="control">
                            <b-button icon-left="edit"
                                      type="is-light"
                                      :loading="is_btn_loading"
                                      @click="setLocalAction('save-and-new')">
                                Save & New
                            </b-button>
                        </p>

                        <p class="control">


                            <b-dropdown aria-role="list" position="is-bottom-left">
                                <button class="button is-light" slot="trigger">
                                    <b-icon icon="caret-down"></b-icon>
                                </button>

                                <b-dropdown-item aria-role="listitem"
                                                 @click="setLocalAction('save-and-close')">
                                    <b-icon icon="check"></b-icon>
                                    Save & Close
                                </b-dropdown-item>

                                <b-dropdown-item aria-role="listitem"
                                                 @click="setLocalAction('save-and-clone')">
                                    <b-icon icon="copy"></b-icon>
                                    Save & Clone
                                </b-dropdown-item>

                                <b-dropdown-item aria-role="listitem"
                                                 @click="resetNewItem()">
                                    <b-icon icon="eraser"></b-icon>
                                    Reset
                                </b-dropdown-item>

                                <b-dropdown-item aria-role="listitem"
                                                 @click="getFaker()">
                                    <b-icon icon="i-cursor"></b-icon>
                                    Fill Dummy Data
                                </b-dropdown-item>

                            </b-dropdown>


                        </p>

                        <p class="control">
                            <b-button tag="router-link"
                                      type="is-light"
                                      :to="{name: 'stores.list'}"
                                      icon-left="times">
                            </b-button>
                        </p>



                    </div>


                </div>

            </header>
            <!--/header-->

            <!--content-->
            <div class="card-content">

                <b-field label="Name" :label-position="labelPosition">
                    <b-input name="stores-name"
                             data-wdio="stores-name"
                             v-model="data.item.name"></b-input>
                </b-field>

                <b-field label="Slug" :label-position="labelPosition">
                    <b-input name="stores-slug"
                             data-wdio="stores-slug"
                             v-model="data.item.slug">
                    </b-input>
                </b-field>
            </div>
            <!--/content-->





        </div>




    </div>
</template>


