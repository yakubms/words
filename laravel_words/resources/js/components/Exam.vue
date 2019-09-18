<template>
    <div>
        <h1 v-if="!isComplete" class="title is-3">テストを受ける</h1>
        <p v-if="!isLoading && !isLoaded && !isComplete">あなたの英単語力を測定します。全50問、所要時間は5～10分ほどです。</p>
        <p v-if="isLoading">読み込み中……。</p>
        <form v-if="!isLoading && !isLoaded && !isComplete" @submit.prevent="startTest">
            <div class="field">
                <label>あなたの英語力</label>
                <div class="control">
                    <div class="select">
                        <select v-model="level">
                            <option value="30">中学レベル</option>
                            <option value="50">高校レベル</option>
                            <option value="70">大学レベル</option>
                            <option value="120">英検一級</option>
                            <option value="200">英単語マニア</option>
                            <option value="300">ネイティブ</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="field">
                <label>選択肢の言語</label>
                <div class="control">
                    <div class="select">
                        <select v-model="language">
                            <option value="eng">英語</option>
                            <option value="jpn">日本語</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="field">
                <div class="control">
                    <button class="button is-primary" type="submit">スタート</button>
                </div>
            </div>
        </form>
        <form id="#q0" method="POST" v-if="questions.length" @submit.prevent="submitAnswer" class="text-lg">
            <p>次の選択肢から最も意味の近いものを選んで下さい。分からない場合はチェックしなくても構いません。</p>
            <p class="is-hidden-mobile">キーボード操作：数字…選択肢を選択(0でスキップ)、Del…一つ戻る、Enter…送信</p>
            <ul>
                <li :id="index | questionId" v-for="(question, index) in questions" :key="index">Q{{ 10 * page + index + 1 }}. {{ question.lemma }}
                    <ol class="list-decimal">
                        <li v-for="(el, key) in question.quiz"><label>{{ key + 1 }}. <input type="radio" :name="index" size="30" v-model="answers[index]" :value="answerData(index, key)" @change="setCurrent(index)">{{ el }}</label></li>
                    </ol>
                </li>
            </ul>
            <button class="button is-primary" type="submit">送信</button>
        </form>
        <h1 v-if="isComplete" class="title">
            お疲れ様でした。あなたの英単語力は{{ level * 100 }}語（{{ level | title }}）です。
        </h1>
    </div>
</template>
<script>
import { mixin } from '../mixin';
import { quizmixin } from '../quizmixin';
export default {
    mixins: [mixin, quizmixin],
    data() {
        return {
            level: "30",
            page: 0,
            isLoading: false,
            isLoaded: false,
            isComplete: false,
            language: 'eng',
            error: {}
        }
    },
    filters: {
        title(level) {
            if (level < 5) {
                return '小学生レベル';
            }
            if (level < 20) {
                return '中学生レベル';
            }
            if (level < 40) {
                return '高校生レベル';
            }
            if (level < 60) {
                return '大学生レベル';
            }
            if (level < 80) {
                return '英検準一級レベル';
            }
            if (level < 100) {
                return 'TOEIC900点レベル';
            }
            if (level < 150) {
                return '英検一級レベル';
            }
            if (level < 200) {
                return 'ネイティブ小学生レベル';
            }
            if (level < 250) {
                return 'ネイティブ中学生レベル';
            }
            if (level < 300) {
                return 'ネイティブ高校生レベル';
            }
            return 'ネイティブレベル';
        }
    },
    methods: {
        startTest() {
            this.isLoading = true;
            // console.log('start test!');
            this.get('/api/words/', {
                level: this.level,
                language: this.language
            });
            this.isLoaded = true;
        },
        async submitAnswer() {
            // console.log('submitting answer...');
            if (this.isLoading || !this.isLoaded) {
                return null;
            }

            this.isLoading = true;
            this.isLoaded = false;

            let filledAnswers = this.fillBlankAnswers();
            let response = await axios.post('/api/words', {
                level: this.level,
                answers: filledAnswers
            });
            console.log(response);
            this.level = response.data.level;
            this.page++;
            this.reset();
            if (this.$route.hash) {
                this.$router.push('/exam');
            }
            if (this.page == 5) {
                this.isLoading = false;
                this.isComplete = true;
                return;
            }
            this.startTest();
        }
    },
}

</script>
