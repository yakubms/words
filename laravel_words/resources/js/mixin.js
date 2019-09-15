export const mixin = {
    methods: {
        submitRequest(request, endpoint, data) {
            return new Promise((resolve, reject) => {
                axios[request](endpoint, data)
                    .then(response => {
                        console.log('success.');
                        this.record(response.data);
                    })
                    .catch(error => {
                        console.log('failed.');
                    })
                    .finally(() => {
                        this.isLoading = false;
                        this.isLoaded = true;
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
            // console.log(field);
            this[field] = data[field];
            // console.log('recorded');
        }
    }
}
