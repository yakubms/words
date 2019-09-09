<template>
    <div>
        <h1 class="text-xl mb-4">テストを受ける</h1>
        <p v-if="!isLoading && !isTakingExam">あなたの英単語力を測定します。所要時間は5分ほどです。</p>
        <p v-if="isLoading">読み込み中……。</p>
        <form v-if="!isLoading && !isTakingExam" class="w-full max-w-sm text-lg" @submit.prevent="startTest">
            <div class="flex items-center border-b border-b-2 border-teal-500 py-2">
                <label class="block mt-4">
                    <span class="text-gray-700">あなたの英語力</span>
                    <select class="form-select mt-1 block w-full" v-model="level">
                        <option value="30">中学レベル</option>
                        <option value="50">高校レベル</option>
                        <option value="70">大学レベル</option>
                        <option value="120">英検一級</option>
                        <option value="200">英単語マニア</option>
                        <option value="300">ネイティブ</option>
                    </select>
                </label>
                <label class="block mt-4">
                    <span class="text-gray-700">選択肢の言語</span>
                    <select class="form-select mt-1 block w-full" v-model="language">
                        <option value="eng">英語</option>
                        <option value="jpn">日本語</option>
                    </select>
                </label>
                <button class="flex-shrink-0 bg-teal-500 hover:bg-teal-700 border-teal-500 hover:border-teal-700 text-sm border-4 text-white py-1 px-2 rounded" type="submit">スタート</button>
            </div>
        </form>
        <form method="POST" v-if="questions.length" @submit.prevent="submitAnswer" class="text-lg">
            <p>次の選択肢から最も意味の近いものを選んで下さい。分からない場合はチェックしなくても構いません。</p>
            <p>キーボードでの選択方法：数字…選択肢を選択(0でスキップ)、Del…一つ戻る</p>
            <ul class="my-3">
                <li :id="index | questionId" v-for="(question, index) in questions" :key="index">Q{{ index + 1 }}. {{ question.lemma }}
                    <ol class="list-decimal my-2">
                        <li v-for="(el, key) in question.quiz"><label><input type="radio" :name="index" size="30" v-model="answers[index]" :value="answerData(index, key)" @change="setCurrent(index)">{{ el }}</label></li>
                    </ol>
                </li>
            </ul>
            <button class="flex-shrink-0 bg-teal-500 hover:bg-teal-700 border-teal-500 hover:border-teal-700 text-sm border-4 text-white py-1 px-2 rounded" type="submit">送信</button>
        </form>
    </div>
</template>
<script>
export default {
    data() {
        return {
            current: 0,
            level: "30",
            isLoading: false,
            isTakingExam: false,
            language: 'eng',
            questions: [],
            answers: [],
            error: {}
        }
    },
    computed: {
        choices() {
            if (!this.questions) {
                return null;
            }
            return this.questions[0].quiz.length;
        }
    },
    filters: {
        questionId(id) {
            return 'q' + (id + 1);
        }
    },
    methods: {
        startTest() {
            this.isLoading = true;
            console.log('start test!');
            this.get('/api/words/', {
                level: this.level,
                language: this.language
            });
        },
        answerData(index, key) {
            return {
                lemma: this.questions[index].lemma,
                answer: this.questions[index].quiz[key],
            }
        },
        keyMonitor(e) {
            if (!this.isTakingExam) {
                return null;
            }
            console.log(e);
            console.log(e.key);
            if (e.key == 'Delete') {
                return this.revert();
            }
            if (0 <= e.key && e.key <= this.choices) {
                return this.answer(e.key);
            }
        },
        setCurrent(index) {
            this.current = index + 1;
        },
        answer(key) {
            console.log(key);
            if (this.answers.length < this.questions.length) {
                this.answers.push({
                    lemma: this.questions[this.current].lemma,
                    answer: this.questions[this.current].quiz[key - 1],
                });
            }
            this.$router.push({ path: this.nextHash() });
        },
        revert() {
            this.answers.pop();
            this.$router.push({ path: this.prevHash() });
        },
        nextHash() {
            if (this.current < this.questions.length) {
                this.current++;
            }
            return '/exam#q' + this.current;
        },
        prevHash() {
            if (this.current > 1) {
                this.current--;
            }
            return '/exam#q' + this.current;
        },
        submitAnswer() {
            this.post('/api/words', {
                level: this.level,
                answers: this.answers
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
                        this.isLoading = false;
                        this.isTakingExam = true;
                    })
                    .catch(error => {
                        console.log('failed.');
                        // this.record(response.data);
                        // reject(error.response);
                        this.isLoading = false;
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
    },
    mounted() {
        window.addEventListener('keyup', e =>
            this.keyMonitor(e)
        );
    }
}

</script>
