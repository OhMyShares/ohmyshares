# Security Policy

## Supported Versions

Only the latest version on the default branch receives security fixes.

## Reporting a Vulnerability

**Please do not report security vulnerabilities through public GitHub issues, discussions, or pull requests.**

Report them privately using GitHub's
[private vulnerability reporting](https://docs.github.com/en/code-security/security-advisories/guidance-on-reporting-and-writing-information-about-vulnerabilities/privately-reporting-a-security-vulnerability):
open the repository's **Security** tab and click **Report a vulnerability**.

Please include as much of the following as you can:

- Type of issue (e.g. SQL injection, XSS, authentication bypass, IDOR)
- Affected endpoint, file path, or component
- Steps to reproduce, ideally with a proof of concept
- Impact and how an attacker might exploit it
- Any suggested fix or mitigation

## What to Expect

- We will acknowledge your report within 14 business days
- We will investigate and keep you informed of progress
- Once the issue is fixed, we will publish a security advisory and credit you unless you prefer to remain anonymous

Please give us reasonable time to address the issue before any public disclosure.

## Scope

In scope:

- The Laravel backend and API
- The Angular frontend
- The plugin system and plugin store integration
- Docker configuration shipped in this repository

Out of scope:

- Vulnerabilities in third-party dependencies that are already publicly known (please report them upstream)
- Issues in self-hosted deployments caused by misconfiguration (e.g. exposed `.env`, `APP_DEBUG=true` in production)
- Denial of service through high-volume traffic
