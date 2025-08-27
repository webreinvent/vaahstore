<script setup>
import {useWishlistStore} from "../../stores/store-wishlists";
import {onMounted, ref, computed} from "vue";
import {useRoute} from "vue-router";

const store = useWishlistStore();
const route = useRoute();
onMounted(async () => {
    if (route.params && route.params.id) {


    }
    await store.getWishlistUsers(route.params.id);
    if (store.user_wishlist_products.length > 0) {
        selectedUserIndex.value = 0
    } else {
        selectedUserIndex.value = null
    }

});
const selectedUserIndex = ref(null)

function toggleProducts(index) {
    selectedUserIndex.value = selectedUserIndex.value === index ? null : index
}


</script>


<template>
    <div class="bg-gray-50 rounded-lg overflow-hidden">


        <div class="flex ">
            <!-- Sidebar -->
            <div class="border-r border-gray-200 min-w-max">
                <!-- Sidebar Header -->
                <div class="px-2 py-[18.5px] border-b border-gray-200 flex flex-wrap justify-between items-center gap-4">
                    <div class="flex items-center gap-1">
                    <Icon
                        icon="tabler:arrow-left"
                        width="20"
                        height="20"

                        @click="store.toList()"
                    />
                    <h2 class="text-base font-semibold text-gray-800">{{store.item?.name || 'loading...'}}</h2>
                    <span class="font-bold bg-[#4f46e51a] py-[2px] px-2 text-[#4f46e5] rounded-md text-[10px]"  v-if="store.list && store.list.total > 0">
                        {{store.wishlist_list_total}}
                    </span>
                    </div>
                    <div class="p-inputgroup max-w-max">
                        <FloatLabel variant="on">
                            <InputText v-model="store.search_term"
                                       @input="store.getWishlistUsers(route.params.id)" size="small" type="text"/>
                            <label for="on_label">Search Users...</label>
                        </FloatLabel>
                        <Button  @click="store.resetSearchTerm(route.params?.id)" size="small" label="Reset">
                        </Button>
                    </div>
                </div>

                <!-- User List -->
                <div class="overflow-y-auto h-[calc(100vh-180px)]">
                    <DataTable :value="store.user_wishlist_products"
                    >

                        <Column>
                            <template #body="prop">
                                <div @click="toggleProducts(prop.index)"
                                     :class="{'bg-gray-100': selectedUserIndex === prop.index}"
                                     class="flex flex-col cursor-pointer">
                                    <div class="flex items-center">
                                        <Avatar :image="prop.data?.avatar" shape="circle" class="mr-2" size="large"
                                                style="background-color: #6366F1; color: white;"/>
                                        <div class="product_desc mx-2 flex flex-col justify-between flex-grow">
                                            <div style="word-break: break-word;">{{ prop.data?.username }}</div>
                                            <p class="text-xs leading-[18px] font-semibold text-gray-400">
                                                {{prop.data?.email}}
                                            </p>
                                            <p class="text-xs leading-[18px] font-semibold text-black-400">
                                                {{prop.data.products?.length}} products in wishlist
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </Column>
                        <template #empty="prop">

                            <div class="no-record-message"
                                 style="text-align: center;font-size: 12px; color: #888;">No records found.
                            </div>

                        </template>
                    </DataTable>
                    <!--paginator-->

                    <!--/paginator-->


                </div>
                <Paginator
                    v-model:rows="store.wishlist_user_rows"
                    :first="((store.wishlist_page??1)-1)*store.wishlist_user_rows"
                    :totalRecords="store.wishlist_list_total"
                    @page="store.wishlistUserPaginate($event,route.params.id)"
                    class="bg-white-alpha-0 pt-2">
                </Paginator>
            </div>

            <!-- Main Content -->
            <div class="flex-1">
                <!-- Header -->

                <div v-if="selectedUserIndex !== null"
                     class="px-2  py-3  border-b border-gray-200 flex flex wrap justify-between items-center">
                    <h2 class="text-base font-semibold text-gray-800">Add Product to <span
                        class=" font-medium text-blue-600">{{store.user_wishlist_products?.[selectedUserIndex]?.email}} </span>
                        Wishlist</h2>
                    <div class="p-inputgroup max-w-max">
                        <AutoComplete
                            name="wishlists-product-search"
                            data-testid="wishlists-product-search"
                            v-model="store.selected_product"
                            option-label="name"
                            :dropdown="true"
                            :complete-on-focus="true"
                            :suggestions="store.product_suggestion"
                            @complete="store.searchProduct($event)"
                            placeholder="Search Product"
                            style="height:36px;"
                            :pt="{
                                dropdown: {
                                  class : '!rounded-r-none'
                                },
                          token: {
                                    class: 'max-w-full'
                                  },
                          removeTokenIcon: {
                                    class: 'min-w-max'
                          },
                          item: { style:
                                {
                                textWrap: 'wrap'
                                }  },
                          panel: { class: 'w-16rem ' }
                            }">
                        </AutoComplete>
                        <Button label="Add to Wishlist"
                                size="small"
                                data-testid="products-save"
                                @click="store.updateUserWishlistProducts(selectedUserIndex,route.params.id,store.selected_product,'add')"
                        />

                    </div>
                </div>
                <!-- Wishlist Content -->
                <div class="">

                    <div v-if="selectedUserIndex !== null">
                        <div class=" bg-gray-50 ml-2 rounded">
                            <DataTable :value="store.user_wishlist_products[selectedUserIndex].products"
                                       paginator :rows="10" :rowsPerPageOptions="[ 10, 20, 50,100]"
                            >
                                <Column field="id" header="ID" :style="{width: store.getIdWidth()}"
                                        class="font-bold text-xs text-gray-400"
                                >
                                </Column>
                                <Column field="name" header="Name">
                                    <template #body="prop">
                                        {{prop.data.name}} - {{prop.data.selected_variation?.name}}
                                    </template>
                                </Column>

                                <Column field="actions" style="width:150px;"
                                        class="font-bold text-xs text-gray-400"
                                        :style="{width: store.getActionWidth() }"
                                        :header="store.getActionLabel()">

                                    <template #body="prop">
                                        <div class="p-inputgroup items-center gap-2">
                                            <Button class="p-button-tiny p-button-danger p-button-text"
                                                    data-testid="wishlists-table-action-trash"
                                                    @click="store.updateUserWishlistProducts(selectedUserIndex, route.params.id, prop.data,'delete')"
                                                    v-tooltip.top="'Delete'"
                                                    :pt="{root: {class: '!bg-none !p-0'}}"
                                            >
                                                <Icon
                                                    icon="mdi:trash-can-outline"
                                                    width="20"
                                                    height="20"
                                                    class="text-[#E02424]"
                                                />
                                            </Button>
                                        </div>

                                    </template>


                                </Column>
                                <template #empty="prop">

                                    <div class="no-record-message"
                                         style="text-align: center;font-size: 12px; color: #888;">No records found.
                                    </div>

                                </template>
                            </DataTable>


                        </div>
                    </div>

                    <!-- Optional: empty state when nothing is selected -->
                    <div v-if="selectedUserIndex === null" class="text-xl text-center text-gray-400 py-12">
                        Select a user to view their wishlist.
                    </div>
                    <!-- Empty State (shown when a user has no items) -->
                    <div class="hidden" id="empty-wishlist">
                        <div class="text-center py-12">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                            <h3 class="mt-4 text-lg font-medium text-gray-900">No items in wishlist</h3>
                            <p class="mt-1 text-sm text-gray-500">This user hasn't added any products to their wishlist
                                yet.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>




