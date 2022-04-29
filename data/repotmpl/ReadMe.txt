DONOT REMOVE THIS FILE

Any file in [selected_template]/hooks/ will be copied to [new_repository]/hooks/ .
Any file in [selected_template]/conf/ will be copied to [new_repository]/conf/ .
Any file in [selected_template]/files/ will be added and committed to svn://[new_repository] .

Sample structure:

template1
  hooks
    pre-commit.cmd
    post-commit.bat
  conf
    authz

template2
  hooks
    pre-commit
    post-commit
  files
    trunk
      1.Documents
        Requirements.docx
      2.Sources
