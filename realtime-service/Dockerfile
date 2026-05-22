FROM node:20-alpine

# Install FFmpeg for video processing workers
RUN apk add --no-cache ffmpeg

WORKDIR /usr/src/app

COPY package*.json ./

RUN npm install --omit=dev

COPY . .

EXPOSE 5000

CMD [ "npm", "start" ]
