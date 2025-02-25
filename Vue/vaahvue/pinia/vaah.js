import {defineStore, acceptHMRUpdate} from 'pinia'
import axios from 'axios'
import qs from "qs";
import dayjs from 'dayjs';
import dayjsPluginUTC from 'dayjs-plugin-utc'

dayjs.extend(dayjsPluginUTC)

export const vaah = defineStore({
    id: 'vaah',
    state: () => ({
        toast: null,
        confirm: null,
        show_progress_bar: false,
    }),
    getters: {},
    actions: {
        ajax: async function (
            url,
            callback = null,
            options= {
                params: null,
                method: 'get',
                query: null,
                headers: null,
                show_success: true,
            }
        ) {

            let self = this;
            let default_option = {
                params: null,
                method: 'get',
                query: null,
                headers: null,
                show_success: true,
            }

            if(options)
            {
                for(let key in options)
                {
                    default_option[key] = options[key];
                }
            }

            let params = default_option.params;
            let method = default_option.method.toLowerCase();
            let query = default_option.query;
            let headers = default_option.headers;
            let show_success = default_option.show_success;



            //To make axios request as ajax request
            if(headers && headers["Authorization"]){
                axios.defaults.headers.common = {
                    "Authorization": headers["Authorization"],
                };
            } else{
                axios.defaults.headers.common = {
                    'X-Requested-With': 'XMLHttpRequest',
                };
            }

            let q = {};

            q.params = query;

            if (headers) {
                q.headers = headers;
            }

            if (method === 'get') {
                params = {
                    params: query,
                };

                q = {};
                axios.interceptors.request.use(
                    function (config) {
                        config.paramsSerializer = function (params) {
                            return qs.stringify(params, {
                                arrayFormat: 'brackets',
                                encode: false,
                                skipNulls: true
                            })
                        }
                        return config;
                    },
                    function (error) {
                        return Promise.reject(error)
                    }
                );
            }

            if (method === 'delete') {
                params = {
                    data: params
                };
            }
            this.show_progress_bar = true;
            let ajax = await axios[method](url, params, q)
                .then(function (response) {
                    self.show_progress_bar = false;
                    self.processResponse(response, show_success);
                    if(callback)
                    {
                        if(response.data && response.data.data)
                        {
                            callback(response.data.data, response);
                        } else{
                            callback(false, response);
                        }
                    }
                    return response;
                }).catch(function (error) {
                    self.show_progress_bar = false;
                    self.processError(error);
                    if(callback)
                    {
                        callback(false, error);
                    }
                    return error;
                });

            return ajax;
        },
        //----------------------------------------------------------
        hasPermission: function (permissions, slug) {
            if(!permissions)
            {
                return false;
            }
            if(permissions.length < 1)
            {
                return false;
            }

            return permissions.indexOf(slug) > -1 ? true : false;
        },
        //----------------------------------------------------------
        processResponse: function(response, show_success)
        {
            if(response && response.data){
                if(response.data.errors)
                {
                    this.toastErrors(response.data.errors);
                }
                if(response.data.messages)
                {
                    this.toastSuccess(response.data.messages);
                }
            }


        },

        //----------------------------------------------------------
        processError: function(error)
        {

            if(error.response
                && error.response.status
                && error.response.status === 419)
            {
                this.toastErrors(['Session Expired. Please sign in again.']);
                location.reload();
                return;
            }

            if(debug === 1)
            {
                alert(error)
                this.toastErrors([error]);
            } else
            {
                // this.toastErrors(['Something went wrong']);
            }
        },
        //----------------------------------------------------------
        getMessageAndDuration(messages)
        {
            let i = 1;
            let list_html = "";
            let duration = 3000;

            if(Object.keys(messages).length > 1)
            {
                for(let k in messages)
                {
                    list_html += i+") "+messages[k]+"<br/>";
                    i++;
                }
            } else
            {
                if(messages[0])
                {
                    list_html += messages[0];
                }
            }

            let chars = list_html.length
            let readable = 10; // readable character per second.

            duration = duration*(chars/readable);

            return {
                html: list_html,
                duration: duration
            };
        },
        //----------------------------------------------------------
        setToast: function (prime_toast)
        {
            this.toast = prime_toast;
        },
        //----------------------------------------------------------
        setConfirm: function (prime_confirm)
        {
            this.confirm = prime_confirm;
        },
        //----------------------------------------------------------
        toastSuccess(messages){
            let data = this.getMessageAndDuration(messages);
            if(data && data.html !== "")
            {
                this.toast.add({
                    severity: 'success',
                    detail: data.html,
                    life: data.duration
                });
            }
        },

        //----------------------------------------------------------
        toastErrors(messages){
            let data = this.getMessageAndDuration(messages);
            if(data && data.html !== "")
            {
                this.toast.add({
                    severity: 'error',
                    detail: data.html,
                    life: data.duration
                });
            }
        },
        //----------------------------------------------------------
        confirmDialog(heading, message, callbackOnAccept, callbackOnReject=null, acceptClass='p-button-danger', icon='pi pi-info-circle')
        {
            this.confirm.require({
                header: heading,
                message: message,
                icon: icon,
                acceptClass: acceptClass,
                accept: () => {
                    callbackOnAccept();
                },
                reject: () => {
                    if(callbackOnReject)
                    {
                        callbackOnReject();
                    }
                }
            });
        },
        //----------------------------------------------------------
        confirmDialogDelete(callbackOnAccept)
        {
            this.confirmDialog('Delete Confirmation', 'Do you want to delete record(s)?', callbackOnAccept);
        },
        //----------------------------------------------------------
        clone: function (source)
        {
            return JSON.parse(JSON.stringify(source));
        },
        //----------------------------------------------------------
        ago: function (value) {
            if(!value)
            {
                return null;
            }
            let time = dayjs(value);
            return time.from();
        },
        //----------------------------------------------------------
        cleanObject: function (obj)
        {
            Object.keys(obj).forEach(key => {
                if (obj[key] === null || obj[key] === 'null' || obj[key] === "") {
                    delete obj[key];
                }
            });

            return obj;
        },

        //----------------------------------------------------------

        copy: function (string)
        {
            if (!navigator.clipboard) {
                this.fallbackCopy(string);
                return;
            }

            let self = this;

            navigator.clipboard.writeText(string).then(function() {
                self.toastSuccess(['Copied']);
            }, function(err) {
                self.toastErrors(['Could not copied | '+err]);
            });

        },
        //----------------------------------------------------------
        fallbackCopy: function (string)
        {
            let textArea = document.createElement("textarea");
            textArea.value = string;

            // Avoid scrolling to bottom
            textArea.style.top = "0";
            textArea.style.left = "0";
            textArea.style.position = "fixed";

            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            let self = this;

            try {
                let successful = document.execCommand('copy');
                let msg = successful ? 'successful' : 'unsuccessful';
                self.toastSuccess(['Copied']);
            } catch (err) {
                self.toastErrors(['Could not copied | '+err]);
            }

            document.body.removeChild(textArea);
        },
        //----------------------------------------------------------
        toLabel: function(str)
        {
            if(typeof str === 'string' )
            {
                str = str.replace(/_/g, " ");
                str = this.toUpperCaseWords(str);
                return str;
            }

        },
        //----------------------------------------------------------
        toUpperCaseWords: function(str)
        {
            if(str)
            {
                return str.charAt(0).toUpperCase() + str.slice(1);
            }
        },
        //----------------------------------------------------------
        removeInArrayByKey: function (array, element, key) {

            if(!Array.isArray(array))
            {
                return false;
            }

            array.map(function(item, index) {

                if(item[key] == element[key])
                {
                    array.splice(index, 1);
                }


            });

            return array;
        },
        //----------------------------------------------------------
        findInArrayByKey: function (array, key, value) {

            if(!Array.isArray(array))
            {
                return false;
            }

            let element = null;

            array.map(function(item, index) {

                if(item[key] == value)
                {
                    element = item;
                }

            });

            return element;
        },
        //----------------------------------------------------------
        strToSlug: function (title,delimiter = '-') {
            let slug = "";
            // Change to lower case
            let titleLower = title.toLowerCase();
            // Letter "e"
            slug = titleLower.replace(/e|é|è|ẽ|ẻ|ẹ|ê|ế|ề|ễ|ể|ệ/gi, 'e');
            // Letter "a"
            slug = slug.replace(/a|á|à|ã|ả|ạ|ă|ắ|ằ|ẵ|ẳ|ặ|â|ấ|ầ|ẫ|ẩ|ậ/gi, 'a');
            // Letter "o"
            slug = slug.replace(/o|ó|ò|õ|ỏ|ọ|ô|ố|ồ|ỗ|ổ|ộ|ơ|ớ|ờ|ỡ|ở|ợ/gi, 'o');
            // Letter "u"
            slug = slug.replace(/u|ú|ù|ũ|ủ|ụ|ư|ứ|ừ|ữ|ử|ự/gi, 'u');
            // Letter "d"
            slug = slug.replace(/đ/gi, 'd');
            // Trim the last whitespace
            slug = slug.replace(/\s*$/g, '');
            // Change whitespace to "-"
            slug = slug.replace(/\s+/g, delimiter);

            return slug;
        },
        //----------------------------------------------------------
        capitalising: function (str) {
            let capitalized = []
            str.split(' ').forEach(word => {
                capitalized.push(
                    word.charAt(0).toUpperCase() +
                    word.slice(1).toLowerCase()
                )
            })
            return capitalized.join(' ')
        },
        //---------------------------------------------------------------------
        localTimeShortFormat: function (value) {

            const utcTime = dayjs.utc(value);

            const date = utcTime.format('DD');

            const current = dayjs();

            const currentDate = current.format('DD');

            if(date === currentDate){
                return utcTime.local().format('hh:mm A');
            } else{

                return utcTime.local().format('MMM DD');
            }


        },
        //----------------------------------------------------------
        toLocalTimeShortFormat: function (value) {

            const utcTime = dayjs.utc(value)

            const date = utcTime.format('DD')
            const dateYear = utcTime.format('YYYY')

            const current = dayjs()

            const currentDate = current.format('DD')
            const currentYear = current.format('YYYY')

            if (date === currentDate) {
                return utcTime.local().format('hh:mm A')
            } else if (dateYear === currentYear) {

                return utcTime.local().format('MMM DD')
            } else {
                return utcTime.local().format('MMM DD YYYY')
            }

        },
        //----------------------------------------------------------
        //----------------------------------------------------------
        //----------------------------------------------------------
    }
})


// Pinia hot reload
if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(vaah, import.meta.hot))
}
