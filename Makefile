.PHONY: build lint bootstrap

build:
	php bin/combine.php data/ > complete.csv

lint:
	php bin/lint.php data/

bootstrap:
	php bin/bootstrap.php data/

checklist:
	php bin/checklist.php data/ > checklist.md