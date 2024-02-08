<!-- PLEASE READ THE FOLLOWING INSTRUCTIONS -->

<!--
Make sure you provide all information and followed all the steps. Then only submit the Merge Request
-->

# version: [X.X.X]

### List of all the tasks


Format of information to be provided

---

#### [Task#123-Title](https://team.webreinvent.com)
**Time invested:** hh:mm format | **Billable**: hh:mm | **Non-Billable**: hh:mm

**Client's comment or requirement**

**How you did it? Exact commit link or code and explain the logic**

**Proof of testers testing, video or image link?**

---

### Hotfix Checklist

[Hotfix Task Link vX.X.X](https://team.webreinvent.com)
- [ ] have you created all subtasks included in this hotfix?
- [ ] did you import & tested all subtasks from the recommended `test list`?
- [ ] did tester test everything on `staging` environment with `production` latest database backup?
- [ ] does this release has anything that will impact the database like `new seeds`, `records that will deleted` or `changes in columns` etc?
- [ ] have you run `npm run production` in `modules` and `themes`?
- [ ] have you changed version of all `module`, `themes` and `project` for `cache bursting`?

---

### Checklist
- [ ] have you updated the `minor` (`x.<update-this>.0`) if there is not `database` change or update `major` (`<update-this>.0.0`) version in `config.php` and `composer.json` of module & theme?
- [ ] verify that the `UI` must match with `design` & `wireframe` if available?
- [ ] did you carefully review the `gitlab-ci.yml` for `master`?
- [ ] Are the subtasks of this release tested and approved by tester?
- [ ] Have you provided all the information in this merge request (MR)?
- [ ] Have you created a task in team for `tester` to test on `production`?
- [ ] Have you informed `tester` to test and remain available until all testing is complete on `production`?
- [ ] Have you taken backup of `production` database, if yes mention the name of backup database?
- [ ] will you make sure to create a `release` in `gitlab` from new `CHANGELOG.md` file once testing is complete on `production` with the version and proper details?
- [ ] you must remember to **create a tag** in `gitlab` after merge request is approved.
- [ ] will you make sure to create a `release` in `gitlab` from new `CHANGELOG.md` file once testing is complete on `production` with the version and proper details?

---

### Steps after Approval

Write steps which manually needs to be followed after approval, like:
**Name:**  ( who will perform these manual steps )
1. Modules or Themes that needs to be enabled of disabled
2. Env variables that needs to be updated
