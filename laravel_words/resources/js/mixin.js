export const mixin = {
    data() {
        return {
            errors: [],
            isLoaded: false,
            isLoading: false
        }
    },
    methods: {
        submitRequest(request, endpoint, data) {
            this.isLoading = true;
            return new Promise((resolve, reject) => {
                axios[request](endpoint, data)
                    .then(response => {
                        console.log('success.');
                        this.record(response.data);
                    })
                    .catch(error => {
                        console.log('failed.');
                        // this.record(error.response);
                        this.errors = error.response.request.status;
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
            let fields = Object.keys(data);
            for (let field of fields) {
                this[field] = data[field];
            }
        },
        async refreshProjects() {
            if (this.$ls.get('projects')) {
                this.level = this.$ls.get('level');
                this.projects = this.$ls.get('projects');
            }
            let response = await axios.get('/api/projects');
            this.$ls.set('level', response.data.level, lsExpiryTime);
            this.$ls.set('projects', response.data.projects, lsExpiryTime);
            this.record(response.data);
        },
    }
}
