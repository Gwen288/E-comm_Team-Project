# CrownCare – Database Migrations

## Purpose
This folder contains SQL files that record changes made to the CrownCare database structure. It helps all team members keep their local databases consistent.

## Folder Structure

```text
database/
└── migrations/
    ├── 001_add_parent_category.sql
    ├── 002_create_product_sizes.sql
    └── 003_update_product_details.sql
```

## Creating a Migration

Whenever you change the database:

1. Save the SQL statements used for the change in a new `.sql` file inside `database/migrations/`.
2. Name the file using the format `OO"whatever incremental number"_description.sql`.
3. Test the SQL before committing it.
4. Push the migration file to GitHub so other team members can access it.

## Applying a Migration

After pulling a new migration from GitHub, team members can apply it using either method:

**Option 1: Import the SQL file**
1. Open phpMyAdmin and select the CrownCare database.
2. Go to the **Import** tab.
3. Choose the migration `.sql` file.
4. Click **Import** or **Go** to execute it.

**Option 2: Run the SQL script**
1. Open phpMyAdmin and select the CrownCare database.
2. Go to the **SQL** tab.
3. Open the migration file and copy its SQL statements.
4. Paste the statements into the SQL editor and execute them.

Apply migrations in chronological order and do not run a migration more than once.

## Team Rule

Every database structure change must have a corresponding SQL migration file committed to GitHub. Do not modify the database structure without sharing the migration with the team.