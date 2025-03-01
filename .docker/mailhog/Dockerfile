FROM alpine:latest

# Install dependencies
RUN apk add --no-cache ca-certificates curl && \
    curl -L https://github.com/mailhog/MailHog/releases/download/v1.0.1/MailHog_linux_amd64 -o /usr/local/bin/MailHog && \
    chmod +x /usr/local/bin/MailHog

# Expose ports
EXPOSE 1025 8025

# Run MailHog
CMD ["MailHog"]
