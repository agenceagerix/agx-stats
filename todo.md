## img alt
revoir le truncate sur le content field après modif

revoir le fait que le content sois push sur intro text uniquement (faire d'avantage de test sur la fonctionnalité des alt img pour tester tout les cas de figure)

fix Le bulk ai qui est bug la génération s'effectue correctement cependant lors de la sauvegarde sa renvoi undefined sur chaque field et le field content ne change pas et faire l'ajout du h1 aussi dans le bulk

Check le bon fonctionnement du force AI

Bug aussi dans l'ajout du H1 il prend le titre avant le passage de l'ia donc il faut gérer si le titre doit être modifier alors on attend de le modifier et on récupére cette valeur et on ajoute. si le titre est déjà optimal alors effectivement on garde celui la
