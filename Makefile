.PHONY: test-all
test-all:
	@echo "Running all tests..."
	@echo "-------------------------------------"
	php vendor/bin/phpunit

.PHONY: test-unit
test-unit:
	@echo "Running unit tests..."
	@echo "-------------------------------------"
	php vendor/bin/phpunit --testsuite unit

.PHONY: test-integration
test-integration:
	@echo "Running integration tests..."
	@echo "-------------------------------------"
	php vendor/bin/phpunit --testsuite integration