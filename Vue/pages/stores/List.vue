<script src="./ListJs.js"></script>
<template>
    <div>

        <div class="columns">

            <div class="column is-6">
                Data
                <pre>{{data.query}}</pre>
            </div>


        </div>


        <div class="columns" v-if="assets && data">

            <!--left-->
            <div class="column" :class="{'is-6': data.view !== 'large'}">

                <!--card-->
                <div class="card" >

                    <!--header-->
                    <header class="card-header">

                        <div class="card-header-title">
                            Stores

                            <b-tag v-if="data.list"
                                   class="has-margin-left-5">
                                {{data.list.total}}
                            </b-tag>

                        </div>


                        <div class="card-header-buttons">
                            <div class="field has-addons is-pulled-right">
                                <p   class="control">
                                    <b-button tag="router-link"
                                              type="is-light"
                                              :to="{name: 'stores.create'}"
                                              icon-left="plus">
                                        Create
                                    </b-button>
                                </p>

                                <p class="control">
                                    <b-button type="is-light"
                                              @click="sync()"
                                              :loading="is_btn_loading"
                                              icon-left="redo-alt">
                                    </b-button>
                                </p>
                            </div>
                        </div>

                    </header>
                    <!--/header-->


                    <!--content-->
                    <div class="card-content">



                        <div class="block" >


                            <!--actions-->
                            <div  v-if="data.view === 'large'" class="level">

                                <!--left-->
                                <div class="level-left" >
                                    <div  class="level-item">
                                        <b-field >

                                            <b-select placeholder="- Bulk Actions -"
                                                      v-model="data.actions.action">
                                                <option value="">
                                                    - Bulk Actions -
                                                </option>
                                                <option
                                                    v-for="option in assets.actions"
                                                    :value="option.slug"
                                                    :key="option.slug">
                                                    {{ option.name }}
                                                </option>
                                            </b-select>

                                            <b-select placeholder="- Select Status -"
                                                      v-if="data.actions.type === 'bulk-change-status'"
                                                      v-model="data.actions.inputs.status">
                                                <option value="">
                                                    - Select Status -
                                                </option>
                                                <option value=1>
                                                    Active
                                                </option>
                                                <option value=0>
                                                    Inactive
                                                </option>
                                            </b-select>


                                            <p class="control">
                                                <button class="button is-light"
                                                        @click="actions">
                                                    Apply
                                                </button>
                                            </p>

                                        </b-field>
                                    </div>
                                </div>
                                <!--/left-->


                                <!--right-->
                                <div class="level-right">

                                    <div class="level-item">

                                        <b-field>

                                            <b-input placeholder="Search"
                                                     v-model="data.query.q"
                                                     @input="delayedSearch"
                                                     @keyup.enter.prevent="delayedSearch"
                                                     icon="search"
                                                     icon-right="close-circle"
                                                     icon-right-clickable
                                                     @icon-right-click="data.query.q = ''">
                                            </b-input>

                                            <p class="control">
                                                <button class="button is-light"
                                                        @click="toggleFilters()">
                                                    <b-icon icon="ellipsis-v"></b-icon>
                                                </button>
                                            </p>

                                            <p class="control">
                                                <button class="button is-light"
                                                        @click="toggleFilters()">
                                                    <b-icon icon="filter"></b-icon>
                                                </button>
                                            </p>

                                        </b-field>

                                    </div>

                                </div>
                                <!--/right-->

                            </div>
                            <!--/actions-->

                            <!--search on small view-->
                            <b-field v-else>

                                <b-input placeholder="Search"
                                         v-model="data.query.q"
                                         @input="delayedSearch"
                                         @keyup.enter.prevent="delayedSearch"
                                         icon="search"
                                         expanded
                                         icon-right="close-circle"
                                         icon-right-clickable
                                         @icon-right-click="data.query.q = ''">
                                </b-input>

                            </b-field>
                            <!--/search on small view-->

                            <!--filters-->
                            <div class="level" v-if="data.show_filters">

                                <div class="level-left">

                                    <div class="level-item">

                                        <b-field label="">
                                            <b-select placeholder="- Select a status -"
                                                      v-model="data.query.filter"
                                                      @input="getList()">
                                                <option value="">
                                                    - Select a status -
                                                </option>
                                                <option value="1">
                                                    Active
                                                </option>
                                                <option value="0">
                                                    Inactive
                                                </option>
                                            </b-select>
                                        </b-field>


                                    </div>

                                    <div class="level-item">
                                        <div class="field">
                                            <b-checkbox v-model="data.query.trashed"
                                                        @input="getList"
                                            >
                                                Include Trashed
                                            </b-checkbox>
                                        </div>
                                    </div>

                                </div>


                                <div class="level-right">

                                    <div class="level-item">

                                        <b-field>
                                            <b-datepicker
                                                position="is-bottom-left"
                                                placeholder="- Select a dates -"
                                                v-model="selected_date"
                                                @input="setDateRange"
                                                range>
                                            </b-datepicker>
                                        </b-field>


                                    </div>

                                </div>

                            </div>
                            <!--/filters-->


                            <!--list-->
                            <div class="container">

                                <ListTable/>


                                <hr style="margin-top: 0;"/>


                                <div class="block" v-if="data.list">
                                    <b-pagination  :total="data.list.total"
                                                   :current.sync="data.list.current_page"
                                                   :per-page="data.list.per_page"
                                                   range-before=3
                                                   range-after=3
                                                   @change="paginate">
                                    </b-pagination>
                                </div>

                            </div>
                            <!--/list-->


                        </div>
                    </div>
                    <!--/content-->


                </div>
                <!--/card-->





            </div>
            <!--/left-->




            <router-view @eReloadList="getList"></router-view>

        </div>




    </div>
</template>


