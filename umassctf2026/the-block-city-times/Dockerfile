FROM maven:3.9-eclipse-temurin-17 AS build
WORKDIR /app
COPY pom.xml .
RUN mvn dependency:go-offline -q
COPY src ./src
RUN mvn package -DskipTests -q

FROM eclipse-temurin:17-jre
WORKDIR /app

COPY --from=build /app/target/config-demo-1.0-SNAPSHOT.jar app.jar

RUN mkdir -p /app/config

EXPOSE 8080

ENTRYPOINT ["java", "-jar", "app.jar"]
