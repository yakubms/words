export const mixin = {
    methods: {
        submitRequest(request, endpoint, data) {
            // for (let field in data) {
            //     if (!data[field]) {
            //         return null;
            //     }
            // }
            console.log('sending request...');
            return new Promise((resolve, reject) => {
                axios[request](endpoint, data)
                    .then(response => {
                        console.log('success.');
                        this.record(response.data);
                        // this.isLoading = false;
                        // this.isTakingExam = true;
                    })
                    .catch(error => {
                        console.log('failed.');
                        // this.record(response.data);
                        // reject(error.response);
                        // this.isLoading = false;
                    });
            });
        },
        get(endpoint, data) {
            this.submitRequest('get', endpoint, {
                params: data
            });
        },
        post(endpoint, data) {
            this.submitRequest('post', endpoint, data);
        },
        patch(endpoint, data) {
            this.submitRequest('patch', endpoint, data);
        },
        delete(endpoint, data) {
            console.log('deleting...');
            this.submitRequest('delete', endpoint, { data: data });
        },
        record(data) {
            console.log(data);
            if (data.error) {
                this.errors.record(data.error);
                return false;
            }
            let field = Object.keys(data);
            this[field] = data[field];
            // this.projects = data;
        }
    },
    mounted() {
        console.log('mixin mounted');
    }
}
