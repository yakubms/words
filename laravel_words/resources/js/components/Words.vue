<template>
    <div>
        <h1 class="title">単語帳</h1>
        <form @submit.prevent="create">
            <div class="field is-grouped">
                <label class="label" for="name">単語帳の名前（任意）</label>
                <div class="control">
                    <input type="text" class="input" name="name" placeholder="単語帳の名前（任意）" v-model="name" maxlength="50">
                </div>
                <label class="label" for="size">単語帳のサイズ(50～500の間で)</label>
                <div class="control">
                    <input type="number" min="50" max="500" step="50" class="input" name="size" v-model="size" required>
                </div>
                <div class="control">
                    <button type="submit" class="button is-link">単語帳を作成</button>
                </div>
            </div>
        </form>
        <table v-if="projectLength" class="table is-hoverable">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>単語帳</th>
                    <th>登録数</th>
                    <th>進捗</th>
                    <th>更新日時</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(project, index) in projects" :key="index">
                    <td>{{ index + 1 }}</td>
                    <td>{{ project.name }}</td>
                    <td>{{ project.task_count }}/{{ project.size }}</td>
                    <td>{{ getCompleteRate(project) }}</td>
                    <td>{{ project.created_at }}</td>
                    <td><button class="button is-primary" @click="show">
                            <strong>編集
                            </strong>
                        </button></td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
<script>
import { mixin } from '../mixin';
export default {
    computed: {
        projectLength() {
            if (!this.projects) {
                return;
            }
            return this.projects.length;
        }
    },
    data() {
        return {
            name: '',
            size: 200,
            projects: []
        }
    },
    mixins: [mixin],
    methods: {
        create() {
            console.log('creating a book...');
            this.post('/api/projects', {
                name: this.name,
                size: this.size,
            });
        },
        record(data) {
            console.log(data);
            if (data.error) {
                this.errors.record(data.error);
                return false;
            }
            console.log(data);
            this.projects = data;
        },
        getCompleteRate(project) {
            if (!project.task_count) {
                return null;
            }
            return parseInt(100 * project.task_complete_count / project.task_count) + '%';
        }
    },
    mounted() {
        if (!this.projects.length) {
            this.projects = this.get('/api/projects');
        }
    }
}

</script>
