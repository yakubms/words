<template>
    <div>
        <h1 class="title">学習</h1>
        <form @submit.prevent="search">
            <div class="field is-grouped">
                <label class="label" for="name">単語検索</label>
                <div class="control">
                    <input type="text" class="input" name="name" placeholder="input a word" v-model="lemma" @input="clearErrors" required>
                </div>
                <div class="control">
                    <button type="submit" class="button is-link">検索</button>
                </div>
            </div>
            <span v-if="errors.length" v-for="error in errors" class="notification is-danger">{{ error }}
            </span>
        </form>
        <div v-if="exampleLength">
            <ol>
                <li v-for="example in examples">{{ example }}</li>
            </ol>
            <form @submit.prevent="store">
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
            lemma: '',
            examples: [],
            errors: []
        }
    },
    methods: {
        search() {
            console.log('searching...');
            this.get('/api/words/' + this.lemma);
        },
        store() {
            console.log('storing...');
            this.post('/api/tasks/', { lemma: this.lemma });
        },
        record(data) {
            console.log(data);
            if (data.error) {
                this.errors.push(data.error);
                return;
            }
            this.examples = data;
        },
        clearErrors() {
            this.errors = [];
        }
    }
}

</script>
