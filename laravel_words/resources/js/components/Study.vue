<template>
    <div>
        <h1 class="title">復習する</h1>
        <form @submit.prevent="startStudy" v-if="!loaded && !loading">
            <div class="field">
                <label for="books">出題する単語帳</label>
                <div class="control">
                    <div class="select">
                        <select id="books" v-model="studyOptions.book">
                            <option value="all">全て</option>
                            <option v-for="book in books" :value="book.id">{{ book.name }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="field">
                <label for="books">出題範囲</label>
                <div class="control">
                    <div class="select">
                        <select v-model="studyOptions.range">
                            <option value="ongoing">覚えていない単語のみ</option>
                            <option value="complete">覚えている単語のみ</option>
                            <option value="all">全て</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="field">
                <label for="books">言語</label>
                <div class="control">
                    <div class="select">
                        <select v-model="studyOptions.lang">
                            <option value="eng">英英</option>
                            <option value="jpn">英和</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="field">
                <label class="label" for="questions">出題数(1～100)</label>
                <div class="control">
                    <input type="number" v-model="studyOptions.questions" min="1" max="100" step="1" class="input" name="questions" placeholder="出題数">
                </div>
            </div>
            <div class="field">
                <label class="label" for="choices">選択肢の数(3～10)</label>
                <div class="control">
                    <input type="number" v-model="studyOptions.choices" min="3" max="10" step="1" class="input" name="choices" placeholder="選択肢の数">
                </div>
            </div>
            <div class="field">
                <div class="control">
                    <button type="submit" class="button is-link">スタート</button>
                </div>
            </div>
        </form>
        <p v-if="loading">Loading...</p>
        <form method="POST" v-if="loaded && !errors.length" @submit.prevent="submitAnswer" class="text-lg">
            <p>次の選択肢から最も意味の近いものを選んで下さい。分からない場合はチェックしなくても構いません。</p>
            <p>キーボードでの選択方法：数字…選択肢を選択(0でスキップ)、Del…一つ戻る</p>
            <ul class="my-3">
                <li :id="index" v-for="(question, index) in questions" :key="index">Q{{ index + 1 }}. {{ question.lemma }}
                    <ol class="list-decimal my-2">
                        <li v-for="(el, key) in question.quiz"><label><input type="radio" :name="index" size="30" v-model="answers[index]" :value="answerData(index, key)" @change="setCurrent(index)">{{ el }}</label></li>
                    </ol>
                </li>
            </ul>
            <button class="button is-primary" type="submit">送信</button>
        </form>
        <ul v-if="errors.length">
            <li v-for="error in errors" class="is-danger">エラー：{{ error }}</li>
        </ul>
    </div>
</template>
<script>
import { mixin } from '../mixin';
import swal from 'sweetalert';
export default {
    mixins: [mixin],
    data() {
        return {
            books: [],
            questions: [],
            answers: [],
            studyOptions: {
                book: 'all',
                questions: '5',
                range: 'ongoing',
                lang: 'eng',
                choices: '4',
                level: '100'
            },
            loading: false,
            loaded: false,
            errors: []
        }
    },
    methods: {
        answerData() {

        },
        startStudy() {
            this.loading = true;
            this.$ls.set('studyOptions', this.studyOptions, LSMONTH * 2);
            this.get('/api/words/quiz', this.studyOptions);
            console.log('start test!!');
            this.loading = false;
            this.loaded = true;
        },
        submitAnswer() {

        },
        // record(data) {
        // console.log(data);
        // if (data.error) {
        // this.errors.push(data.error);
        // this.searchWord = '';
        // return;
        // }
        // this.lemma = this.searchWord;
        // this.searchWord = '';
        // this.level = data.level;
        // this.examples = data.examples;
        // },
        // clearErrors() {
        // this.errors = [];
        // },

        setBooks() {
            if (this.$ls.get('projects')) {
                this.studyOptions.level = this.$ls.get('level');
                this.books = this.$ls.get('projects').map(el => {
                    return {
                        id: el.id,
                        name: el.name
                    };
                });
            }
        }
    },
    mounted() {
        if (this.$ls.get('studyOptions')) {
            this.studyOptions = this.$ls.get('studyOptions');
        }
        this.setBooks();

        if (!this.books) {
            axios.get('/api/projects')
                .then(response => {
                    console.log(response);
                    this.$ls.set('projects', response.data.projects);
                    this.$ls.set('level', response.data.projects);
                    this.setBooks();
                });
        }
    }
}

</script>
