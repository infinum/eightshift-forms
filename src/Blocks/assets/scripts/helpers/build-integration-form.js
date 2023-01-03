import React from 'react';
import { snakeCase } from 'lodash';
import { select } from "@wordpress/data";
import { STORE_NAME, ucfirst } from '@eightshift/frontend-libs/scripts';

const namespace = select(STORE_NAME).getSettingsNamespace();

function getBlockName(component, key) {
	return `${component}${ucfirst(key)}`;
}

function skipAttributes(key, value) {
	if (value === '') {
		return true;
	}

	const newComponent =  snakeCase(key).split('_');

	const output = [];

	newComponent.forEach((element) => {
		const manifest = select(STORE_NAME).getComponent(element)?.attributes[key]?.default;

		if (element === 'order' || element === 'use') {
			output.push(true);
			return;
		}

		if (typeof manifest !== 'undefined' && manifest === value) {
			output.push(true);
			return;
		}

		output.push(false);
	});

	if (output.includes(true)) {
		return true;
	}

	return false;
}

function buildInnerOutputData(element) {
	const {
		component,
	} = element;

	delete element['component'];

	const inner = {
		name: `${namespace}/${component}`,
		attributes: {},
		innerBlocks: [],
	};

	for (const [key, value] of Object.entries(element)) {
		if (skipAttributes(key, value)) {
			continue;
		}

		inner.attributes[getBlockName(component, key)] = value;
	}
};

export const buildOutputData = (formData) => {
	let output = [];
	formData.forEach((element, index) => {
		const {
			component,
		} = element;

		delete element['component'];

		const inner = {
			name: `${namespace}/${component}`,
			attributes: {},
			innerBlocks: [],
		};

		for (const [key, value] of Object.entries(element)) {
			if (key === 'blockSsr') {
				inner.attributes[key] = true;
				continue;
			}

			if (skipAttributes(key, value)) {
				continue;
			}

			if (Array.isArray(value)) {
				inner.innerBlocks = buildInnerOutputData(element);
			} else {
				inner.attributes[getBlockName(component, key)] = value;
			}
		}

		output.push(inner);
	});

	return output;
}

export const outputData = (data) => {
	return data.map((item) => {
		return [
			item.name,
			item.attributes,
			item.innerBlocks,
		];
	});
};
