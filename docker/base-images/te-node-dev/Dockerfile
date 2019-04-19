ARG NODE_VERSION=11.14.0

FROM node:${NODE_VERSION}-alpine

# GIT + Python
#RUN apk --no-cache add git
RUN apk --no-cache add g++ gcc libgcc libstdc++ linux-headers make python

# https://github.com/JeffreyWay/laravel-mix/issues/1539
RUN apk --no-cache add bash libpng-dev
#           gcc \
#           g++ \
#           make \



#######################################
#######################################

#Copy Entry point
COPY entrypoint-app.sh /usr/local/bin/entrypoint-app
RUN chmod +x /usr/local/bin/entrypoint-app
#	&& dos2unix /usr/local/bin/entrypoint-app

# Create app directory
WORKDIR /app
ENV PATH node_modules/.bin:$PATH


#RUN npm install sqlite3 --save

ENTRYPOINT ["entrypoint-app"]
CMD [ "ping", "127.0.0.1" ]