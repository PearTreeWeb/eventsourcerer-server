setup:
	./docker/create-docker-network.sh
	docker compose up -d php
	docker compose exec php composer install
	docker compose exec php bin/console app:setup:create_certificates
	docker compose exec php bin/console tailwind:build
	@$(MAKE) trust-ca
	@$(MAKE) up
	docker compose exec php bin/console app:setup:load-projections
	docker compose exec php bin/console app:setup:load-templates
	@echo ""
	@echo "✅ Setup complete. Open https://eventsourcerer.docker.localhost in your browser."

trust-ca:
	@CA=certs/rootCA.pem; \
	if [ ! -f $$CA ]; then echo "Root CA not found at $$CA"; exit 1; fi; \
	UNAME=$$(uname); \
	case "$$UNAME" in \
		Darwin) \
			echo "Adding $$CA to macOS System keychain (sudo required)..."; \
			sudo security add-trusted-cert -d -r trustRoot -k /Library/Keychains/System.keychain $$CA ;; \
		Linux) \
			echo "Adding $$CA to Linux system trust store (sudo required)..."; \
			if [ -d /usr/local/share/ca-certificates ]; then \
				sudo cp $$CA /usr/local/share/ca-certificates/eventsourcerer-rootCA.crt && sudo update-ca-certificates; \
			elif [ -d /etc/pki/ca-trust/source/anchors ]; then \
				sudo cp $$CA /etc/pki/ca-trust/source/anchors/eventsourcerer-rootCA.pem && sudo update-ca-trust; \
			else \
				echo "Could not detect CA trust directory. Please install $$CA manually."; exit 1; \
			fi ;; \
		MINGW*|MSYS*|CYGWIN*) \
			echo "Adding $$CA to Windows Root store (admin shell required)..."; \
			certutil -addstore -f "Root" $$CA ;; \
		*) \
			echo "Unsupported OS: $$UNAME. Please add $$CA to your trust store manually."; exit 1 ;; \
	esac

up:
	./docker/create-docker-network.sh
	docker compose up -d
	docker compose exec php bin/console d:m:m -n
	docker compose exec php composer install
	docker compose exec php bin/console importmap:install

down:
	docker compose stop

in:
	docker compose exec -it php /bin/bash
