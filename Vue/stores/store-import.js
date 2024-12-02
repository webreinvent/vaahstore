import {ref, watch} from 'vue'
import {acceptHMRUpdate, defineStore} from 'pinia'
import qs from 'qs'
import {vaah} from '../vaahvue/pinia/vaah'
import CSV from "dom-csv.js";

let model_namespace = 'VaahCms\\Modules\\Store\\Models\\Import';

let base_url = document.getElementsByTagName('base')[0].getAttribute("href");

let ajax_url = base_url + "/store/imports";


export const useImportStore = defineStore({
    id: 'import',
    state: () => ({
        base_url: base_url,
        ajax_url: ajax_url,
        model: model_namespace,
        assets_is_fetching: true,
        app: null,
        assets: null,
        list: {
            headers: [],
            columns: [],
            records: [],
        },
        item: null,
        is_list_loading: null,

        list_bulk_menu: [],
        selected_type : null,
        import_type : null,
        file : null,
        selected_file : null,
        mapped_fields : [],
        csv_data : null,
        csv_fields : [],
        router: null,
        errors :{
            file : null,
            import_type : null,
        },
        pageIndex: 0,
        result: null,
        steps: [
            {
                label: 'Upload',
                to: "/import/upload",
                pageIndex: 0,
            },
            {
                label: 'Map',
                to: "/import/map",
                pageIndex: 1,
            },
            {
                label: 'Preview',
                to: "/import/preview",
                pageIndex: 2,
            },
            {
                label: 'Result',
                to: "/import/result",
                pageIndex: 3,
            },
            {
                label: 'Export',
                to: "/import/export",
                pageIndex: 4,
            },
        ],
        is_override: false,
        type: null,
        is_importing: false,

        label : 'Click to upload',
        selected_records : [],

    }),
    getters: {

    },
    actions: {
        //---------------------------------------------------------------------
        async onLoad(route) {
            this.pageIndex = 0;
            /**
             * Set initial routes
             */
            this.route = route;

            /**
             * Set the type from query
             */
            this.setTypeFromQuery();
        },
        //---------------------------------------------------------------------
        async getAssets() {

            if (this.assets_is_fetching === true) {
                this.assets_is_fetching = false;

                await vaah().ajax(
                    this.ajax_url +'/assets',
                    this.afterGetAssets,
                );
            }
        },
        //---------------------------------------------------------------------
        afterGetAssets(data, res) {
            if (data) {
                this.assets = data;
                this.type = data.types;


            }
        },
        //---------------------------------------------------------------------
        onSelect() {

            if (this.file.files.length > 0) {
                this.errors.file = null;
                this.selected_file = this.file.files[0];
            }

        },
        //---------------------------------------------------------------------
        readCSV() {

            if (this.selected_file) {
                CSV.fetch({file: this.selected_file}).then((csv) => {
                    this.csv_data = csv;
                    this.mapFields();
                    this.updateCSVFields();
                    this.updateCSVHeaders();
                    this.nextPage();
                });
            }
        },
        //---------------------------------------------------------------------
        updateCSVHeaders() {
            this.mapped_fields.forEach((field, index) => {
                this.csv_fields.forEach((csv_field, csv_index) => {
                    const fieldLabel = field.label ? field.label.toLowerCase() : null;
                    const csvFieldLabel = csv_field.label ? this.normaliseLabel(csv_field.label).toLowerCase() : null;

                    if (fieldLabel && csvFieldLabel && fieldLabel === csvFieldLabel) {
                        this.mapped_fields[index].csv_field_index = csv_index;
                    }
                });
            });
        },

        //---------------------------------------------------------------------
        normaliseLabel(label) {
            if (!label) {
                return null;
            }
            label = label.toLowerCase();

            label = label.replace(/_/g, ' ');
            label = label.replace(/taxonomy id /, '');
            label = label.replace(/ Id/, '');
            label = label.replace(/\b\w/g, (match) => match.toUpperCase());

            return label;
        },
        //---------------------------------------------------------------------
        updateCSVFields() {
            let csv_fields = [
            ];
            this.csv_data.fields.forEach((field, index) => {
                field = {
                    "label": field,
                    "index": index,
                }
                csv_fields.push(field);
            });

            this.csv_fields = csv_fields;
        },
        //---------------------------------------------------------------------
        setTypeFromQuery: function () {
            if (this.route && this.route.query && this.route.query.type) {
                this.import_type = this.route.query.type;
                this.setActiveType();
            }
        },
        //---------------------------------------------------------------------
        setActiveType: function () {
            let type = this.getType();
            this.type = type;
        },
        //---------------------------------------------------------------------
        mapFields(){
            let map = [];
            this.type.fields.forEach((field, index) => {
                field = {
                    "label": this.normaliseLabel(field.label),
                    "column": field.column,
                    "csv_field_index": null,
                }
                map.push(field);
            });
            this.mapped_fields = map;
        },
        //---------------------------------------------------------------------
        getType() {
            if(this.import_type) {
                return vaah().findInArrayByKey(this.assets.types, 'name', vaah().capitalising(this.import_type));
            }
            return null;
        },
        //---------------------------------------------------------------------
       async preview() {
            this.resetList();
            this.mapped_fields.forEach((field, index) => {
                this.list.headers.push({
                    "field": field.column,
                    "label": field.label,
                });
                this.list.columns.push(field.column);
            });
            this.csv_data.records.forEach((record, index) => {
                let row = {};
                this.mapped_fields.forEach((field, index) => {
                    row[field.column] = record[field.csv_field_index]
                    if(row.is_active){
                        row.is_active = 1
                    }
                    if(row.date){
                        const pattern = /^\d{4}-\d{2}-\d{2}$/;
                        if (!pattern.test(row.date)) {
                            vaah().toastErrors(['Date format is not is no correct']);
                        }
                    }
                });
                this.list.records.push(row);
            });
        },
        //---------------------------------------------------------------------
        resetList() {
            this.list = {
                headers: [],
                columns: [],
                records: [],
            }
        },
        //---------------------------------------------------------------------
        async importData() {

            if (!this.list.records.length) {
                vaah().toastErrors(['Record does not exist']);
                return false;
            }
            this.is_importing = true;
            let options = {
                method: 'POST',
                params: {
                    list: this.list,
                    import_type: this.import_type,
                    is_override: this.is_override,
                }
            };
            await vaah().ajax(
                this.ajax_url + '/import/data',
                this.importDataAfter,
                options
            );
        },
        //---------------------------------------------------------------------
        importDataAfter(data, res) {
            this.is_importing = false;
            if (data) {
                this.result = [data];
                this.nextPage();
            }
        },
        //---------------------------------------------------------------------
        clearErrors() {
            this.errors = {
                file: null,
                import_type: null,
            }
        },
        //---------------------------------------------------------------------
        nextPage() {
            this.pageIndex++;
            this.$router.push(this.steps[this.pageIndex].to);

        },

        //---------------------------------------------------------------------
        prevPage() {
            this.pageIndex--;
            this.$router.push(this.steps[this.pageIndex].to);
        },
        //---------------------------------------------------------------------
        restart: function () {
            this.clearErrors();
            this.label = 'Click to Upload';
            this.import_type = null;
            this.csv_data = null;
            this.csv_fields = [];
            this.mapped_fields = [];
            this.result = null;
            this.resetList();
            this.pageIndex = 0;
            this.selected_file = null;
            if(this.file)
            {
                this.file.files = [];
            }

            this.$router.push({name: 'import.upload'});
        },
        //---------------------------------------------------------------------
        redirectIfInvalid(){
            if(!this.csv_data){
                this.$router.push({ name : 'import.upload' });
            }
        },
        //---------------------------------------------------------------------
        downloadSampleFile() {
            if(this.type){
                const url = this.ajax_url + '/get/sample-file';
                window.open(url, '_blank');
            }
        },
        //---------------------------------------------------------------------
        onChangeImportType() {
            let type = this.getType();
            if(!type){
                this.type = null;
                this.errors.import_type = 'Invalid import type';
                return;
            }
            this.type = type;
            this.errors.import_type = null;
        },
        //---------------------------------------------------------------------
        setActivePageIndex(to){
            if (!to) return;
            let step = vaah().findInArrayByKey(this.steps, 'to', to);
            if(step){
                this.pageIndex = step.pageIndex;
            }
        },

        //---------------------------------------------------------------------

        updateColumnName(field)
        {

            switch (field) {
                case 'vh_st_store_id':
                    return 'Store';
                case 'vh_st_brand_id':
                    return 'Brand';
                case 'taxonomy_id_product_type':
                    return 'Product Type';
                case 'description':
                    return 'Description';
                case 'taxonomy_id_product_status':
                    return 'Status';
                case 'name':
                    return 'Name';
                case 'slug':
                    return 'Slug';

                case 'summary':
                    return 'Summary';

                case 'details':
                    return 'Details';
                case 'status_notes':
                    return 'Status Notes';
                case 'seo_title':
                    return 'SEO Title';
                case 'seo_meta_description':
                    return 'SEO Meta Description';
                case 'seo_meta_keyword':
                    return 'SEO Meta Keywords';
                case 'is_featured_on_home_page':
                    return 'Featured on Home page';
                case 'is_featured_on_category_page':
                    return 'Featured on Category page';
                case 'quantity':
                    return 'Quantity';
                case 'available_at':
                    return 'Availablity Date';
                case 'launch_at':
                    return 'Launch Date';
                case 'is_active':
                    return 'Is Active';
                default:
                    return field;
            }
        },

        //---------------------------------------------------------------------



        //-------------------------------------------------------------------
        removeRecord(record) {

            const index = this.list.records.indexOf(record);
            if (index !== -1) {
                this.list.records.splice(index, 1);
            }
        },
        //------------------------------------------------------------------

        deleteSelected()
        {
            this.list.records = this.list.records.filter(record =>
                !this.selected_records.some(selected =>
                    JSON.stringify(selected) === JSON.stringify(record)
                )
            );
            this.selected_records = [];
            vaah().toastSuccess(["Deleted successfully"]);
        },

        //-----------------------------------------------------------------

        getListBulkMenu()
        {
            this.list_bulk_menu = [
                {
                    label: 'Trash All',
                    command: async () => {
                        await this.deleteAllRecords();
                    }
                },
                {
                    label: 'Restore All',
                    command: async () => {
                        await this.preview();
                        await this.selectTaxonomy();
                    }
                },
            ];
        },

        //-----------------------------

        deleteAllRecords()
        {
            if(this.list.records && this.list.records.length > 0)
            {
                this.list.records = [];
            }
            else {
                vaah().toastErrors(['No record exist to delete']);
            }

        },

        //------------------------------------------------

        convertToSlug(text) {

            return text.trim().toLowerCase().replace(/\s+/g, '-');
        },

        //--------------------------------------------------

        async selectTaxonomy() {
            for (const data of this.list.records) {
                this.processData(data);
            }
        },

        //------------------------------------------------

        processData(data) {

            // Process vh_st_store_id
            if (data.vh_st_store_id) {
                let store = this.assets.stores.find(s => s.id === parseInt(data.vh_st_store_id));

                if (!store) {
                    const store_slug = vaah().strToSlug(data.vh_st_store_id.toString());
                    store = this.assets.stores.find(s => s.slug === store_slug);
                }

                data.vh_st_store_id = store ? store.id : null;
            } else {
                data.vh_st_store_id = null;
            }

            // Process vh_st_brand_id
            if (data.vh_st_brand_id) {
                const brand_slug = vaah().strToSlug(data.vh_st_brand_id.toString());
                const brand = this.assets.brands.find(b => b.slug === brand_slug);
                data.vh_st_brand_id = brand ? brand.id : null;
            } else {
                data.vh_st_brand_id = null;
            }

            // Process taxonomy_id_product_status
            if (data.taxonomy_id_product_status && this.assets.taxonomy?.status?.length > 0) {
                const status_slug = vaah().strToSlug(data.taxonomy_id_product_status.toString());
                const status = this.assets.taxonomy.status.find(s => s.slug === status_slug);
                data.taxonomy_id_product_status = status ? status.id : null;
            } else {
                data.taxonomy_id_product_status = null;
            }

            // Process taxonomy_id_product_type
            if (data.taxonomy_id_product_type && this.assets.taxonomy?.types?.length > 0) {
                const type_slug = vaah().strToSlug(data.taxonomy_id_product_type.toString());
                const type = this.assets.taxonomy.types.find(t => t.slug === type_slug);
                data.taxonomy_id_product_type = type ? type.id : null;
            } else {
                data.taxonomy_id_product_type = null;
            }

            // Handle quantity
            if (!data.quantity) {
                data.quantity = null;
            }

        },




        //-----------------------------------------------------------



        openSelectedFile()
        {
            if (this.selected_file) {
                window.open(this.selected_file.url, '_blank');
            }

            },

        //------------------------------------------------

        toProducts()
        {
            this.clearErrors();
            this.label = 'Click to Upload';
            this.import_type = null;
            this.csv_data = null;
            this.csv_fields = [];
            this.mapped_fields = [];
            this.result = null;
            this.resetList();
            this.pageIndex = 0;
            this.selected_file = null;
            if(this.file)
            {
                this.file.files = [];
            }
            this.$router.push({name: 'products.index'});
        },

        //-------------------------------------------------

        downloadInvalidRecords(data)
        {
            const blob = new Blob([data.content], { type: data.headers['Content-Type'] });
            const url = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = 'invalid-records.csv';
            link.click();
            window.URL.revokeObjectURL(url);
        },

        //---------------------------------------------------

        //------------------------------------------------------------------

        showDescription(description)
        {
            return description;
        },



    },
});



// Pinia hot reload
if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useImportStore, import.meta.hot))
}
