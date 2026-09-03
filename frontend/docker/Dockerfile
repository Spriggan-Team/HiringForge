FROM node:20-alpine

WORKDIR /app

# Copy dependences
COPY package.json package-lock.json* ./

RUN npm install

# Copy sorce code
COPY . .

EXPOSE 5174

# Run frontend
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0", "--port", "5174"]