<!-- PLEASE READ THE FOLLOWING INSTRUCTIONS -->

<!--
Make sure you provide all information and followed all the steps. Then only submit the Merge Request
-->

### List of all the tasks

Format of information to be provided

---

#### [Task#123-Title](https://team.webreinvent.com)
**Time invested:** hh:mm format | **Billable**: hh:mm | **Non-Billable**: hh:mm

**Client's comment or requirement**

**Proof of your testing, video, or image link?**

---
**If task has sub-taks, create individual video for each sub tasks or delete this section**

eg:

**Sub Task #3** - Sub task requirement

Video: https://team.webreinvent.com

**Sub Task #3** - Sub task requirement

Video: https://team.webreinvent.com

---

### Additional Detail

**Document PR Link :**

**Document URL :**

**Test-Video from Approver's side :**

**QA Progress Report :**

---

### Self Review Checklist

#### Naming Conventions
> For a detailed information click [here](https://docs.vaah.dev/guide/code#naming-conventions)

- [ ] Are your Controller names `meaningful`, `singular` and have `Controller` suffix?
- [ ] Are your Model names `singular` and named after the table they are modelling?
- [ ] Are your Table names `snake_case` and `plural`?
- [ ] Are your Table Column names `snake_case` without model name?
- [ ] Are your Seeder names `descriptive` about which table they are seeding?
- [ ] Are your method/function names `short`, `descriptive` and `camelCased`?
- [ ] Are your variable names `short`, `descriptive` and `snake_cased`?

#### Constants and Configs
> For a detailed information click [here](https://docs.vaah.dev/guide/code#naming-conventions)

- [ ] API keys, Secret Keys, URL's etc., are stored as env variables.
- [ ] I am accessing env variables via config.
- [ ] I have used config and language files, constants instead of text in the code .
- [ ] I have not hardcoded any value.

#### Fat methods  and CRUD
> For a detailed information click [here](https://docs.vaah.dev/guide/code#laravel-community-guidelines-for-good-coding-practices)

- [ ] All requests are being validated
- [ ] I am not using raw SQL queries. I am using `Eloquent` queries.
- [ ] DB related logic are in `Eloquent models` or in `Repository`/`Helper` classes.
- [ ] I have not included business logic in Controllers. They are in `Repository`/`Helper` classes.
- [ ] My methods adhere to the `Single Responsibility Principle`.
- [ ] My code doesn't contain extensive if-else nesting. I am checking negative conditions first.
- [ ] I have removed all unwanted commented code.
- [ ] I have done my tech debt analysis and any unavoidable code leading to tech debt is marked with `@todo` .

#### Other practices

- [ ] All my migrations have `down` method that reverses operations performed by `up` method.
- [ ] Any new column added/altered in table has been also updated in the `fillable` property of its model.
- [ ] For seeders that are not populating sample data, it is not truncating table before seeding again.
- [ ] For seeders that are not populating sample data, the data is being populated from a separate external file.
- [ ] There are no business logic or queries in routes.
- [ ] I have not put queries in Blade templates.
- [ ] I have removed all debug code.

#### Nuxt coding practices

- [ ] My Vue Components names are `short`, describes what it's intended use is and is in `PascalCase`
- [ ] All the components exclusive to the page are in the page's `components` folder
- [ ] I have preferred using Composition API.
- [ ] I have preferred using `<script setup>` tag instead of the `setup()`.
- [ ] I have used asyncData or fetch hook (avoid mounted or created hooks) to fetch any initial data that will make the component load later than page.
- [ ] I have marked any component that does not have reactive elements as `functional`.
- [ ] I have used `key` for any `v-for` used.
- [ ] I have not used `v-for` and `v-if` in the same element.
- [ ] I have declared `props` with `camelCase` but used in templates with `kebab-case`.
- [ ] I have validated my `props` with good definition, and have set there default values
- [ ] I have added a `Readme.md` file for each component I have created.
- [ ] All my page specific methods and variables are in the page's store.

---

### Feature Merge Request Checklist

- [ ] have you rebased your `feature` with `develop`?
- [ ] have run `vite build` or `npx vite build` after `rebase` with `develop` branch in all your `Modules` & `Themes`? Notes, you have to reactivate modules and themes to publish assets.
- [ ] have you updated the `patch` (`x.x.<update-this>`) version `config.php` and `composer.json` of module & theme?
- [ ] have you read all the `comments` & `notes` in `wireframe` and verified that you have taken care of that?
- [ ] verify that the `UI` must match with `design` & `wireframe` if available?
- [ ] have you applied `data-testid` tag to `links`, `forms` & all `form inputs` like `input`, `select`?
- [ ] did you verify the latest commit of `develop` exists in your `feature` branch after rebase?
- [ ] have you tested the `feature` as per the client requirement?
- [ ] did you follow the proper naming conventions in your code?
- [ ] have you commented on your code with proper explanation?



