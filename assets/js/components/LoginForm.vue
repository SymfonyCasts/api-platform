<template>
    <form v-on:submit.prevent="handleSubmit">
        <div v-if="error" class="alert alert-danger">
            {{ error }}
        </div>
        <div class="form-group">
            <label for="exampleInputEmail1">Email address</label>
            <input type="email" v-model="email" class="form-control" id="exampleInputEmail1"
                   aria-describedby="emailHelp" placeholder="Enter email">
        </div>
        <div class="form-group">
            <label for="exampleInputPassword1">Password</label>
            <input type="password" v-model="password" class="form-control"
                   id="exampleInputPassword1" placeholder="Password">
        </div>
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="exampleCheck1">
            <label class="form-check-label" for="exampleCheck1">I like cheese</label>
        </div>
        <button type="submit" class="btn btn-primary" v-bind:class="{ disabled: isLoading }">Log in</button>
    </form>
</template>

<script>
    import axios from 'axios';

    export default {
        data() {
            return {
                email: '',
                password: '',
                error: '',
                isLoading: false
            }
        },
        props: ['user'],
        methods: {
            handleSubmit() {
                this.isLoading = true;
                this.error = '';

                axios
                    .post('/login', {
                        email: this.email,
                        password: this.password
                    })
                    .then(response => {
                        this.$emit('user-authenticated', response.headers.location);
                        this.email = '';
                        this.password = '';
                    }).catch(error => {
                        if (error.response.data.error) {
                            this.error = error.response.data.error;
                        } else {
                            this.error = 'Unknown error';
                        }
                    }).finally(() => {
                        this.isLoading = false;
                    })
            },
        },
    }
</script>

<style scoped lang="scss">
</style>
