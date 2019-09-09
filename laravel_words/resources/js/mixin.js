export const mixin = {
    methods: {
        submitRequest(request, endpoint, data) {
            // for (let field in data) {
            //     if (!data[field]) {
            //         return null;
            //     }
            // }
            console.log('posting...');
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
            console.log('hogehoge');
            this.submitRequest('post', endpoint, data);
        },
        record(data) {
            console.log(data);
            if (data.error) {
                this.errors.record(data.error);
                return false;
            }
            this.projects = data;
        }

    },
    mounted() {
        console.log('mixin mounted');
    }
}
