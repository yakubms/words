<template>
    <div>
        <h1>テストを受ける</h1>
        <p>あなたの英単語力を測定します。所要時間は5分ほどです。</p>
        <form v-if="!isTakingExam" class="w-full max-w-sm" @submit.prevent="startTest">
            <div class="flex items-center border-b border-b-2 border-teal-500 py-2">
                <label class="block mt-4">
                    <span class="text-gray-700">あなたの英語力を教えてください</span>
                    <select class="form-select mt-1 block w-full" v-model="level">
                        <option value="30">中学レベル（英検三級）</option>
                        <option value="50">高校レベル（英検二級）</option>
                        <option value="70">大学レベル（英検準一級）</option>
                        <option value="120">英検一級</option>
                        <option value="200">英単語マニア</option>
                        <option value="300">ネイティブ</option>
                    </select>
                </label>
                <button class="flex-shrink-0 bg-teal-500 hover:bg-teal-700 border-teal-500 hover:border-teal-700 text-sm border-4 text-white py-1 px-2 rounded" type="submit">スタート</button>
            </div>
        </form>
        <ul v-if="questions.length">
            <li v-for="(question, index) in questions" :key="index">{{ question.lemma }}
                <ul>
                    <li v-for="el in question.quiz">{{ el }}</li>
                </ul>
            </li>
        </ul>
    </div>
</template>
<script>
export default {
    data() {
        return {
            level: "30",
            isTakingExam: false,
            questions: [],
            error: {}
        }
    },
    methods: {
        startTest() {
            this.isTakingExam = true;
            console.log('start test!');
            this.get('/api/words/', {
                level: this.level
            });
        },
        submitRequest(request, endpoint, data) {
            for (let field in data) {
                if (!data[field]) {
                    return null;
                }
            }
            return new Promise((resolve, reject) => {
                axios[request](endpoint, data)
                    .then(response => {
                        console.log('success.');
                        this.record(response.data);
                        // resolve(response.data);
                    })
                    .catch(error => {
                        console.log('failed.');
                        // this.record(response.data);
                        // reject(error.response);
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
        record(data) {
            console.log(data);
            if (data.error) {
                this.errors.record(data.error);
                return false;
            }
            this.questions = data;
        },
    }
}

</script>
