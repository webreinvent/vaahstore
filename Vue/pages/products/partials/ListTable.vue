<script src="./ListTableJs.js"></script>
<template>
    <div>
        <div v-if="data && data.list">
            <b-table :data="data.list.length ? [] : data.list.data"
                     :checked-rows.sync="data.action.items"
                     checkbox-position="left"
                     :checkable="isViewLarge()"
                     :hoverable="true"
                     :row-class="setRowClass">

                <template >
                    <b-table-column field="id" label="ID"
                                    :visible="isViewLarge()"
                                    v-slot="props">
                        {{ props.row.id }}
                    </b-table-column>

                    <b-table-column field="name" label="Name"
                                    :visible="isViewLarge()"
                                    v-slot="props">
                        <b-tooltip label="Copy"
                                   type="is-dark">
                        <vh-copy class="text-copyable"
                                 :data="props.row.slug"
                                 :label="props.row.name"
                                 @copied="copiedData">
                        </vh-copy>
                        </b-tooltip>
                    </b-table-column>

                    <b-table-column field="type" label="Type" v-slot="props">
                        <b-tag v-if="props.row.taxonomy_id_product_type">
                            {{ props.row.type.name }}
                        </b-tag>
                    </b-table-column>


                    <b-table-column field="is_active" label="Active"
                                    :visible="isViewLarge()"
                                    v-slot="props">

                        <b-button @click="changeStatus(props.row)"
                                  :type="props.row.is_active?'is-success':'is-danger'"
                                  size="is-small" >
                            {{ props.row.is_active?'Yes':'No' }}
                        </b-button>


                    </b-table-column>


                    <b-table-column field="updated_at"
                                    label="Updated At"
                                    :visible="isViewLarge()"
                                    width="130"
                                    v-slot="props">
                        <b-tooltip :label="props.row.updated_at"
                                   type="is-dark">
                        <small>
                            {{ $vh.ago(props.row.updated_at) }}
                        </small>
                        </b-tooltip>
                    </b-table-column>


                    <b-table-column field="actions" label=""
                                    v-slot="props"
                                    width="80">

                        <b-tooltip label="Configure Product" type="is-dark">
                            <b-button size="is-small"
                                      @click="setActiveProduct(props.row)"
                                      icon-left="cog">
                            </b-button>
                        </b-tooltip>

                        <b-tooltip label="View" type="is-dark">
                            <b-button size="is-small"
                                      @click="setActiveItem(props.row)"
                                      icon-left="chevron-right">
                            </b-button>
                        </b-tooltip>


                    </b-table-column>


                </template>

                <template slot="empty">
                    <section class="section">
                        <div class="content has-text-grey has-text-centered">
                            <p>Nothing here.</p>
                        </div>
                    </section>
                </template>

            </b-table>
        </div>
    </div>

</template>

