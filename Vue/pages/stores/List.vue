<script src="./ListJs.js"></script>
<template>
    <div>

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
                                              @click="getList()"
                                              :loading="data.is_list_loading"
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


                                        <b-field>

                                            <div class="control">
                                                <b-dropdown class="is-paddingless"
                                                            aria-role="list">

                                                    <template #trigger="{ active }">
                                                        <b-button
                                                            type="is-outlined"
                                                            :label="'Actions ('+data.action.items.length+')'"
                                                            :icon-right="active ? 'angle-up' : 'angle-down'"
                                                            icon-left="edit">
                                                        </b-button>
                                                    </template>

                                                    <b-dropdown-item @click="updateList('active')"
                                                                     aria-role="listitem">
                                                        Mark as active
                                                    </b-dropdown-item>

                                                    <b-dropdown-item @click="updateList('inactive')"
                                                                     aria-role="listitem">
                                                        Mark as inactive
                                                    </b-dropdown-item>
                                                    <hr class="dropdown-divider">

                                                    <b-dropdown-item @click="updateList('trash')"
                                                                     aria-role="listitem">
                                                        <b-icon icon="trash"></b-icon>
                                                        Trash
                                                    </b-dropdown-item>

                                                    <b-dropdown-item @click="confirmDelete()"
                                                                     aria-role="listitem">
                                                        <b-icon type="is-danger is-light"
                                                                icon="trash-alt"></b-icon>
                                                        Delete
                                                    </b-dropdown-item>


                                                </b-dropdown>
                                            </div>


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
                                                     @icon-right-click="clearSearch">
                                            </b-input>

                                        </b-field>

                                    </div>


                                    <div class="level-item">

                                        <b-field grouped>
                                        <b-field>
                                            <div class="control">
                                                <b-dropdown class="is-paddingless"
                                                        position="is-bottom-left"
                                                        aria-role="list">

                                                <template #trigger>
                                                    <b-button
                                                        type="is-outlined"
                                                        :label="'Filters ('+data.count_filters+')'"
                                                        icon-left="filter">
                                                    </b-button>
                                                </template>


                                                <b-dropdown-item
                                                    aria-role="menu-item"
                                                    :focusable="false"
                                                    custom>

                                                    <div class="columns is-paddingless is-multiline is-marginless filters" style="width: 400px;">

                                                        <div class="column is-6">
                                                            <div class="filter mb-2">
                                                                <p class="mb-1"><b>Is Active</b></p>

                                                                <b-field>
                                                                    <b-radio v-model="data.query.filter.is_active"
                                                                             :native-value="null"
                                                                             name="is_active">
                                                                        All
                                                                    </b-radio>
                                                                </b-field>

                                                                <b-field>
                                                                    <b-radio v-model="data.query.filter.is_active"
                                                                             native-value="true"
                                                                             name="is_active">
                                                                        Only Active
                                                                    </b-radio>
                                                                </b-field>

                                                                <b-field>
                                                                    <b-radio v-model="data.query.filter.is_active"
                                                                             native-value="false"
                                                                             name="is_active">
                                                                        Only Inactive
                                                                    </b-radio>
                                                                </b-field>

                                                                <hr/>
                                                            </div>

                                                            <div class="filter mb-2">
                                                                <p class="mb-1"><b>Include</b></p>

                                                                <b-field>
                                                                    <b-radio v-model="data.query.filter.trashed"
                                                                             :native-value="null"
                                                                             name="trashed">
                                                                        Exclude Trashed
                                                                    </b-radio>

                                                                </b-field>

                                                                <b-field>
                                                                    <b-radio v-model="data.query.filter.trashed"
                                                                             native-value="include"
                                                                             name="trashed">
                                                                        Include Trashed
                                                                    </b-radio>

                                                                </b-field>

                                                                <b-field>
                                                                    <b-radio v-model="data.query.filter.trashed"
                                                                             native-value="only"
                                                                             name="trashed">
                                                                        Only Trashed
                                                                    </b-radio>
                                                                </b-field>
                                                                <hr/>
                                                            </div>

                                                        </div>
                                                        <div class="column is-6">
                                                            <div class="filter mb-2">
                                                                <p class="mb-1"><b>Sort By</b></p>

                                                                <b-field>
                                                                    <b-radio v-model="data.query.sort"
                                                                             :native-value="null"
                                                                             name="sort">
                                                                        None
                                                                    </b-radio>
                                                                </b-field>

                                                                <b-field>
                                                                    <b-radio v-model="data.query.sort"
                                                                             native-value="updated_at"
                                                                             name="sort">
                                                                        Updated (Ascending)
                                                                    </b-radio>
                                                                </b-field>

                                                                <b-field>
                                                                    <b-radio v-model="data.query.sort"
                                                                             native-value="updated_at:desc"
                                                                             name="sort">
                                                                        Updated (Descending)
                                                                    </b-radio>
                                                                </b-field>
                                                                <hr/>
                                                            </div>


                                                            <div class="filter mb-2">
                                                                <p class="mb-1"><b>Page</b></p>

                                                                <b-field>
                                                                    <b-field>
                                                                        <b-input
                                                                            native-min="1"
                                                                            v-model="data.query.page">
                                                                        </b-input>
                                                                    </b-field>
                                                                </b-field>

                                                                <hr/>
                                                            </div>
                                                        </div>

                                                    </div>

                                                </b-dropdown-item>


                                            </b-dropdown>
                                            </div>


                                            <b-tooltip label="Reset Filters"
                                                       type="is-dark">
                                            <p class="control" >

                                                <b-button @click="resetQuery()"
                                                          icon-left="times">
                                                </b-button>
                                            </p>
                                            </b-tooltip>

                                        </b-field>



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



                            <!--list-->
                            <div class="container">

                                <ListTable @eReloadList="getList"></ListTable>


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


