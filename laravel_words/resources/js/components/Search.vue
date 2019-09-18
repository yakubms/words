<template>
    <div>
        <h1 class="title">単語検索</h1>
        <form @submit.prevent="search">
            <div class="field">
                <label class="label" for="name">単語を入力</label>
                <div class="control">
                    <input type="text" class="input" name="name" placeholder="input a word" v-model="searchWord" @input="clearErrors" required>
                </div>
                <div class="control">
                    <button type="submit" class="button is-link">検索</button>
                </div>
            </div>
            <span v-if="errors.length" v-for="error in errors" class="notification is-danger column is-3">{{ error }}
            </span>
        </form>
        <div class="container" v-if="exampleLength">
            <p>{{ lemma }} レベル: {{ level }}</p>
            <div class="columns">
                <ol class="menu-list column is-5">
                    <li v-for="example in examples">{{ example }}</li>
                </ol>
                <ol class="menu-list column is-5">
                    <li v-for="meaningJp in meaningsJp">{{ meaningJp }}</li>
                </ol>
            </div>
            <form @submit.prevent="onStore">
                <div class="field">
                    <div class="control">
                        <button type="submit" class="button is-link">単語帳に登録</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>
<script>
import { mixin } from '../mixin';
import swal from 'sweetalert';
export default {
    mixins: [mixin],
    computed: {
        exampleLength() {
            if (!this.examples) {
                return 0;
            }
            return this.examples.length;
        }
    },
    data() {
        return {
            searchWord: '',
            lemma: '',
            level: '',
            examples: [],
            meaningsJp: [],
            errors: []
        }
    },
    methods: {
        search() {
            console.log('searching...');
            this.get('/api/words/' + this.searchWord);
        },
        onStore() {
            console.log('storing...');
            axios.get('/api/tasks/' + this.lemma)
                .then(response => {
                    if (response.data) {
                        this.onDuplicate();
                        return;
                    }
                    this.register();
                })
                .catch(error => console.log(error));
        },
        async register() {
            await axios.post('/api/tasks/', { lemma: this.lemma });
            swal('登録しました。');
            this.refreshProjects();
        },
        onDuplicate() {
            swal("この単語は単語帳に登録済みですが登録しますか？", {
                    buttons: {
                        not: "登録しない",
                        onduplicate: {
                            text: "登録する",
                            value: "onduplicate"
                        }
                    }
                })
                .then(value => {
                    if (value == 'onduplicate') {
                        this.register();
                    }
                });
        },
        record(data) {
            console.log(data);
            if (data.error) {
                this.errors.push(data.error);
                this.searchWord = '';
                return;
            }
            this.lemma = this.searchWord;
            this.searchWord = '';
            this.level = data.level;
            this.examples = data.examples;
            this.meaningsJp = data.meaningsJp;
        },
        clearErrors() {
            this.errors = [];
        }
    }
}

</script>
