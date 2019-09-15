export const quizmixin = {
    data() {
        return {
            current: 0,
            questions: [],
            answers: [],
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
        reset() {
            this.current = 0;
            this.questions = [];
            this.answers = [];
        },
        answerData(index, key) {
            return {
                id: this.questions[index].id,
                lemma: this.questions[index].lemma,
                answer: this.questions[index].quiz[key],
            }
        },
        keyMonitor(e) {
            if (!this.isLoaded) {
                return null;
            }
            if (e.key == 'Delete') {
                return this.revert();
            }
            if (e.key == 'Enter') {
                return this.submitAnswer();
            }
            if (0 <= e.key && e.key <= this.choices) {
                return this.answer(e.key);
            }
        },
        setCurrent(index) {
            this.current = index + 1;
        },
        pushAnswer(index, key) {
            if (!parseInt(key)) {
                this.answers.push({
                    id: this.questions[index].id,
                    lemma: this.questions[index].lemma,
                    answer: '',
                });
                return;
            }
            this.answers.push({
                id: this.questions[index].id,
                lemma: this.questions[index].lemma,
                answer: this.questions[index].quiz[key - 1],
            });
        },
        answer(key) {
            if (this.answers.length < this.questions.length) {
                this.pushAnswer(this.current, key);
                // console.log(this.questions[this.current].id);
            }
            // console.log('answering...');
            let nextHash = this.nextHash();
            if (!nextHash) {
                return;
            }

            this.$router.push({ path: nextHash });
        },
        revert() {
            let prevHash = this.prevHash();
            if (!prevHash) {
                return;
            }
            this.answers.pop();
            this.$router.push({ path: prevHash });
        },
        nextHash() {
            if (this.current >= this.questions.length) {
                return;
            }
            this.current++;
            return this.$route.path + '#q' + this.current;
        },
        prevHash() {
            if (this.current < 1) {
                return;
            }
            this.current--;
            return this.$route.path + '#q' + this.current;
        },
        fillBlankAnswers() {
            if (this.questions.length - this.answers.length > 0) {
                let i = this.answers.length;
                for (i; i < this.questions.length; i++) {
                    this.pushAnswer(i);
                }
            }

            let filledAnswers = JSON.parse(JSON.stringify(this.answers));
            return filledAnswers.map((answer, index) => {
                if (!answer) {
                    return {
                        id: this.questions[index].id,
                        lemma: this.questions[index].lemma,
                        answer: '',
                    }
                }
                return answer;
            });
        },
        submitAnswer() {
            this.post('/api/words', {
                level: this.level,
                answers: this.answers
            });
        },

    },
    filters: {
        questionId(id) {
            return 'q' + (id + 1);
        }
    },
    mounted() {
        window.addEventListener('keyup', e =>
            this.keyMonitor(e)
        );
    }
}
