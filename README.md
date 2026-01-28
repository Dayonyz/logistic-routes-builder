# Logistic Routes Builder

### Deploy

```
make build
make install
```

### Run Application

```
make start
make ssh
php app.php app:generate-routes
```

## Problem Statement

This project is based on a real-life problem.

In practice, a single employee often has to combine multiple roles at the same time: driver, courier, loader, and technical specialist. In addition, the work is performed under difficult conditions caused by war, poor road quality, weather conditions, and general unpredictability of movement.

Due to physical workload and time pressure, the employee is often unable to properly fill in daily route reports that include travel times, distances, and fuel consumption. However, at the end of each month, these reports must be submitted with official stamps and signatures from accounting and several responsible authorities. Therefore, the reports must be clean, consistent, realistic, and believable in order to be accepted and approved.

A close friend of mine faced exactly this problem and asked me to help automate the process.

The **only critical requirement** for this task was accurate and realistic **fuel consumption** and **travel time between destinations**, so that by the end of the month the employee clearly knows:
- how much fuel was actually consumed;
- how many working days were in the month.

> ⚠️ **Important notice**  
> This application is **not intended** and **must not be used** for fraud, deception, or fictitious fuel write-offs. Its sole purpose is to simplify the generation of daily movement reports based on realistic constraints and real fuel consumption data.

---

## Solution Overview

The application is implemented as a **native PHP CLI script**.

During execution, the user provides:
- the number of working days in the month;
- a realistic recommended fuel consumption range;
- the actual total fuel consumption for the month (usually within the suggested range);
- the probability of good weather during the past month, calculated as:  
  `(number of good weather days / total working days) * 100`.

Based on this input, the system generates a **set of daily route reports**, including:
- movement between destinations;
- travel time tracking;
- fuel consumption;
- distances.

The generated daily reports are balanced in such a way that their **total fuel consumption exactly matches the real monthly fuel input**.

All cities, villages, and the distance matrix between them are **real**.

---

## Architecture

The application follows:
- **SOLID principles**;
- clear **domain separation**;
- several **GoF design patterns**, including:
    - Singleton;
    - Strategy;
    - Builder.

The project is framework-independent and focuses on clean, explicit domain logic.

---

## Key Concepts

### General Movement Rules

- The vehicle always starts from **Zlatopil** between **09:00 and 09:45**.
- The vehicle always returns to **Zlatopil**.
- Movement inside the cities **Zlatopil** and **Lozova** is always explicitly recorded as separate route legs:

```
Zlatopil - Zlatopil    Departure at: 09:15   Arrival at: 09:20   Distance (km): 2   Fuel (L): 0.2
```

- Arrival to and departure from Lozova are always recorded as two separate entries:
```
Lozova - Lozova        Departure at: 11:55   Arrival at: 12:00   Distance (km): 3   Fuel (L): 0.3
Lozova - Lozova        Departure at: 12:55   Arrival at: 13:00   Distance (km): 3   Fuel (L): 0.3
```

## Route Types

There are three route types:

- routes visiting Lozova;

- routes visiting Blyzniuky;

- routes visiting villages only (excluding Lozova and Blyzniuky).

Monthly route distribution probabilities:

- Lozova — 17%;

- Blyzniuky — 17%;

- Villages — 66%.

## Route Composition Rules

- Before visiting Lozova or Blyzniuky, the vehicle may optionally visit one random village.

- After visiting Lozova or Blyzniuky, the vehicle returns to Zlatopil.

- Village-only routes must visit exactly two villages.

- Between destination visits, there are mandatory work breaks ranging from 20 minutes to 1 hour.

## Distance Matrix

Each connection between destinations stores four values:
```
"kys": {
    "g": 15,
    "n": 4,
    "b": 14,
    "s": 33
}
```

Where:

- g — good road segment (km);

- n — normal road segment (km);

- b — bad road segment (km);

- s — total distance (km).

Fuel consumption and travel time are calculated using different coefficients per road type.

## Weather and Randomness

- Weather conditions affect speed and fuel consumption with a configurable probability.

- Additional random fluctuations are applied to simulate:

  - traffic lights;

  - short stops;

  - minor delays.

## Input Parameters

- Number of working days in the month;

- Recommended fuel consumption range;

- Actual total fuel consumption;

- Good weather probability calculated as:
```
(number of good weather days / total working days) * 100
```

## Output

Example of generated output:
```
DAY 1: Route type - VILLAGES
Zlatopil - Zlatopil        Departure at: 09:00   Arrival at: 09:05   Distance (km): 2   Fuel (L): 0.2
Zlatopil - Novoberetske    Departure at: 09:05   Arrival at: 09:20   Distance (km): 21  Fuel (L): 1.6
Novoberetske - Mykhailivka Departure at: 09:55   Arrival at: 11:10   Distance (km): 51  Fuel (L): 4.6
Mykhailivka - Zlatopil     Departure at: 12:10   Arrival at: 13:00   Distance (km): 30  Fuel (L): 2.9
Zlatopil - Zlatopil        Departure at: 13:00   Arrival at: 13:05   Distance (km): 2   Fuel (L): 0.2

------------------------- Totals: Distance 106 km, Fuel 10 L -------------------------
```

## Notes

⚠️ Legal disclaimer!

This application must not be used for illegal activities, fraud, or falsification of reports.
All legal and criminal responsibility lies solely with the user.

The project is published for educational and demonstration purposes only, as an example of real-world domain modeling and software design in PHP.
