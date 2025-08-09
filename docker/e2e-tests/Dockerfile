FROM node:20-slim

# Set working directory
WORKDIR /app

# Install dependencies required for Playwright browsers
RUN apt-get update && apt-get install -y --no-install-recommends \
    libglib2.0-0 \
    libnss3 \
    libnspr4 \
    libatk1.0-0 \
    libatk-bridge2.0-0 \
    libcups2 \
    libdrm2 \
    libdbus-1-3 \
    libxcb1 \
    libxkbcommon0 \
    libx11-6 \
    libxcomposite1 \
    libxdamage1 \
    libxext6 \
    libxfixes3 \
    libxrandr2 \
    libgbm1 \
    libpango-1.0-0 \
    libcairo2 \
    libasound2 \
    libatspi2.0-0 \
    libwayland-client0 \
    wget \
    xvfb \
    fonts-noto-color-emoji \
    fonts-freefont-ttf \
    && rm -rf /var/lib/apt/lists/*

# Install Playwright globally
RUN npm init -y && \
    npm install -g playwright@latest && \
    npx playwright install --with-deps chromium && \
    npm cache clean --force

# Set environment variables for Playwright
ENV PLAYWRIGHT_SKIP_BROWSER_DOWNLOAD=1
ENV PLAYWRIGHT_BROWSERS_PATH=/ms-playwright
ENV DISPLAY=:99

# Add a script to start Xvfb for headless browser testing
COPY docker/e2e-tests/start-xvfb.sh /usr/local/bin/start-xvfb.sh
RUN chmod +x /usr/local/bin/start-xvfb.sh

# Create a non-root user to run tests
RUN groupadd -r playwright && useradd -r -g playwright -G audio,video playwright \
    && mkdir -p /home/playwright && chown -R playwright:playwright /home/playwright \
    && chown -R playwright:playwright /app

USER playwright

# Set up entrypoint
ENTRYPOINT ["/usr/local/bin/start-xvfb.sh"]
CMD ["npx", "playwright", "test"]
