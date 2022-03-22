<script src="./ViewJs.js"></script>
<template>
    <div class="column" v-if="assets && data.item">

        <div class="card" >

            <!--header-->
            <header class="card-header">

                <div class="card-header-title">
                </div>

                <div class="card-header-buttons">

                    <div class="field has-addons is-pulled-right">
                        <p class="control">
                            <b-button @click="$vaah.copy(data.item.id)"  type="is-light">
                                <small><b>#{{data.item.id}}</b></small>
                            </b-button>
                        </p>
                        <p class="control">
                            <b-button icon-left="edit"
                                      type="is-light"
                                      tag="router-link"
                                      :to="{name:'stores.update', params:{id: data.item.id}}">
                                Edit
                            </b-button>
                        </p>

                        <p class="control">
                            <b-dropdown aria-role="list" position="is-bottom-left">
                                <button class="button is-light" slot="trigger">
                                    <b-icon icon="caret-down"></b-icon>
                                </button>
                                <b-dropdown-item aria-role="listdata.item"
                                                 v-if="!data.item.deleted_at"
                                                 @click="updateItem.item('trash')">
                                    <b-icon icon="trash"></b-icon>
                                    Trash
                                </b-dropdown-item>
                                <b-dropdown-item v-if="data.item.deleted_at"
                                                 @click="updateItem.item('restore')">
                                    <b-icon icon="trash-restore"></b-icon>
                                    Restore
                                </b-dropdown-item>
                                <b-dropdown-item @click="confirmDelete()">
                                    <b-icon icon="eraser"></b-icon>
                                    Delete
                                </b-dropdown-item>
                            </b-dropdown>
                        </p>
                        <p class="control">
                            <b-button type="is-light"
                                      @click="resetItem()"
                                      icon-left="times">
                            </b-button>
                        </p>
                    </div>
                </div>

            </header>
            <!--/header-->

            <b-notification type="is-danger"
                            :closable="false"
                            class="is-light is-small"
                            v-if="data.item.deleted_at">
                Deleted {{$vh.ago(data.item.deleted_at)}}
            </b-notification>

            <!--content-->
            <div class="card-content is-paddingless ">
                <div class="block" >
                    <div class="b-table">
                        <div class="table-wrapper">
                            <table class="table is-hoverable">
                                <tbody>
                                <tr>
                                    <th align="right" width="30%">Name</th>
                                    <td colspan="2">
                                        {{data.item.name}}
                                    </td>
                                </tr>
                                <tr>
                                    <th align="right">Slug</th>
                                    <td colspan="2">
                                        {{data.item.slug}}
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>


                </div>
            </div>
            <!--/content-->
        </div>
    </div>
</template>


